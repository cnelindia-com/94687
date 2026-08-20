<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Models\Background;
use App\Extensions\FashionStudio\System\Models\FashionModel;
use App\Extensions\FashionStudio\System\Models\Pose;
use App\Extensions\FashionStudio\System\Models\Wardrobe;
use App\Extensions\FashionStudio\System\Models\CustomModel;
use App\Models\UserOpenai;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PhotoShootController extends BaseFashionStudioController
{
    private array $generationData = [];

    public function index(): View
    {
        $models = DB::table('statick_models')
            ->orderBy('rank', 'asc')
            ->get();

        $backgroundset = DB::table('static_background')
            ->orderBy('rank', 'asc')
            ->get();

        $poseset = DB::table('static_pose')
            ->orderBy('rank', 'asc')
            ->get();
        
        return view('fashion-studio::photoshoots.index', compact('models', 'backgroundset', 'poseset'));
    }

   
    public function myPhotoshoots(): View
    {
        $userId = Auth::id();
        
        // ✅ Check: User team ka owner hai ya nahi
        $ownerTeam = DB::table('teams')
            ->select('id')
            ->where('user_id', $userId)
            ->first();
        
        $isTeamOwner = (bool) $ownerTeam;

        // ✅ Check: User team member hai aur kya role hai
        $teamMemberInfo = DB::select("
            SELECT tm.team_role, tm.role, tm.status
            FROM team_members tm
            WHERE tm.user_id = ?
            LIMIT 1
        ", [$userId]);

        $teamMember = $teamMemberInfo[0] ?? null;
        
        // ✅ Viewer = team_role === 2 OR role === 'viewer'
        $isViewer = $teamMember && (
            (int)$teamMember->team_role === 2 || 
            strtolower($teamMember->role ?? '') === 'viewer'
        );

        // ✅ Creator = team_role === 1 OR role === 'creator' (aur viewer nahi)
        $isCreator = $teamMember && (
            (int)$teamMember->team_role === 1 || 
            strtolower($teamMember->role ?? '') === 'creator'
        ) && !$isViewer;

        return view('fashion-studio::photoshoots.my', [
            'isTeamOwner' => $isTeamOwner,
            'isViewer' => $isViewer,
            'isCreator' => $isCreator,
            'userId' => $userId,
        ]);
    }

    public function loadImages(Request $request): JsonResponse
    {

        $perPage = 16;
        $page = $request->get('page', 1);
        $userId = Auth::id();

        $type = $request->get('type', 'all');
        $isOwner = false;
        $teamId = auth()->user()?->team_id;
        $userId = Auth::id();

        // Pehle users.team_id check karo
        if (!$teamId) {
            // Check if user is TEAM OWNER (owner ka users.team_id NULL hota hai)
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $userId)
                ->first();
            if ($ownerTeam) {
                $teamId = $ownerTeam->id;
                $isOwner = true;
            }
        }

        // Agar team owner nahi hai to team_members se lo
        if (!$teamId) {
            $memberTeam = DB::table('team_members')
                ->select('team_id')
                ->where('user_id', $userId)
                ->first();
            if ($memberTeam) {
                $teamId = $memberTeam->team_id;
            }
        }

        // Agar abhi bhi teamId mil gaya hai to double check karo ki owner hai ya nahi
        if ($teamId && !$isOwner) {
            $ownerCheck = DB::table('teams')
                ->select('id')
                ->where('id', $teamId)
                ->where('user_id', $userId)
                ->first();
            $isOwner = !empty($ownerCheck);
        }

        // Team owner ka user_id find karo
        $teamOwnerId = null;
        if ($teamId) {
            $teamOwner = DB::table('teams')
                ->select('user_id')
                ->where('id', $teamId)
                ->first();
            if ($teamOwner) {
                $teamOwnerId = $teamOwner->user_id;
            }
        }

        $query = UserOpenai::with('createdByUser')
            ->where(function ($q) use ($teamId, $teamOwnerId, $userId) {
            if ($teamId) {
                // Team ki images (team_id = current team) ya team owner ki purani images bhi dikhao
                $q->where('team_id', $teamId);
                if ($teamOwnerId) {
                    $q->orWhere(function($subQ) use ($teamOwnerId) {
                        $subQ->where('user_id', $teamOwnerId)
                             ->whereNull('team_id');
                    });
                }
            } else {
                // Apni images
                $q->where('user_id', $userId);
            }
        })
->where('is_fashion_studio', true)
->with('createdByUser')
->orderBy('created_at', 'desc');

        if ($type === 'videos') {
            $query->where('response', 'FS-VIDEO')
                ->whereIn('status', [ImageStatusEnum::completed->value, ImageStatusEnum::processing->value]);
        } elseif ($type === 'images') {
            $query->where('response', 'FS')
                ->where('status', ImageStatusEnum::completed->value)
                ->where('output', '!=', null);
        } else {
            $query->where(function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('response', 'FS')
                        ->where('status', ImageStatusEnum::completed->value)
                        ->where('output', '!=', null);
                })->orWhere(function ($subQ) {
                    $subQ->where('response', 'FS-VIDEO')
                        ->whereIn('status', [ImageStatusEnum::completed->value, ImageStatusEnum::processing->value]);
                });
            });
        }

        $images = $query->paginate($perPage, ['*'], 'page', $page);

        $now = now();
        $today = $now->copy()->startOfDay();
        $yesterday = $now->copy()->subDay()->startOfDay();

        $thumbnails = [];

        foreach ($images as $image) {
            $isVideo = false;
            $isImage = false;
            $isProcessing = $image->status === ImageStatusEnum::processing->value;

            if ($image->response === 'FS-VIDEO') {
                $isVideo = true;
            } elseif (! empty($image->output)) {
                $extension = strtolower(pathinfo($image->output, PATHINFO_EXTENSION));
                $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm', 'mkv']);
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            }

            if (! $isVideo && ! $isImage) {
                $isImage = true;
            }

            if ($type === 'images' && ! $isImage) {
                continue;
            }
            if ($type === 'videos' && ! $isVideo) {
                continue;
            }

            $imageUrl = $image->output;
            $imageWithThumbnail = $image->toArray();
            
            // For S3/DigitalOcean Spaces images, create thumbnail on S3 and use proxy for viewing
            if (!$isProcessing && $imageUrl && (str_contains($imageUrl, 'digitaloceanspaces.com') || str_contains($imageUrl, '.s3.') || str_contains($imageUrl, env('AWS_URL', '')))) {
                // Use proxy URL for main image to avoid CORS
                $imageWithThumbnail['url'] = route('dashboard.user.fashion-studio.proxy.image', ['url' => $imageUrl]);
                // Create and use thumbnail from S3
                $imageWithThumbnail['thumbnail'] = $this->getLocalThumbnail($imageUrl);
            } else {
                $imageWithThumbnail['url'] = $isProcessing ? '/themes/default/assets/img/loading.svg' : $imageUrl;
                $imageWithThumbnail['thumbnail'] = $isProcessing ? '/themes/default/assets/img/loading.svg' : ThumbImage($imageUrl);
            }
            
            // Also add a direct URL for operations that need the original (like delete)
            $imageWithThumbnail['original_url'] = $isProcessing ? '' : $image->output;

            $createdAt = $image->created_at;

            $imageWithThumbnail['is_today'] = $createdAt->greaterThanOrEqualTo($today) && $createdAt->lessThan($now->copy()->addDay()->startOfDay());
            $imageWithThumbnail['is_yesterday'] = $createdAt->greaterThanOrEqualTo($yesterday) && $createdAt->lessThan($today);
            $imageWithThumbnail['is_older'] = $createdAt->lessThan($yesterday);
            $imageWithThumbnail['format_date'] = $image->created_at->diffForHumans();
            $imageWithThumbnail['is_image'] = $isImage;
            $imageWithThumbnail['is_video'] = $isVideo;
            $imageWithThumbnail['is_processing'] = $isProcessing;

            // Creator ka naam add karo
            $creatorUser = $image->createdByUser;
            $imageWithThumbnail['created_by_id'] = $image->created_by ?: $image->user_id;
            $imageWithThumbnail['created_by_name'] = $creatorUser ? ($creatorUser->name ?? 'Unknown') : ($image->user?->name ?? 'Unknown');
            $imageWithThumbnail['created_by_avatar'] = $creatorUser ? ($creatorUser->avatar ?? null) : ($image->user?->avatar ?? null);
            $imageWithThumbnail['team_id'] = $image->team_id;
            $imageWithThumbnail['can_delete'] = false;

            if ($isOwner && $teamId && $image->team_id == $teamId) {
                $imageWithThumbnail['can_delete'] = true;
            } elseif ($image->created_by == $userId || $image->user_id == $userId) {
                $imageWithThumbnail['can_delete'] = true;
            }

            $thumbnails[] = $imageWithThumbnail;
        }

        return response()->json([
            'images'  => $thumbnails,
            'hasMore' => $images->hasMorePages(),
            'page'    => $images->currentPage(),
            'total'   => $images->lastPage(),
        ]);
    }

    public function cropImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_id'     => 'required|integer',
            'image_data'   => 'required|string',
            'width'        => 'nullable|numeric',
            'height'       => 'nullable|numeric',
            'aspect_ratio' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        $imageId = $request->input('image_id');

        // Pehle user_id se dhundho
        $userOpenai = UserOpenai::where('id', $imageId)
            ->where('user_id', $userId)
            ->where('is_fashion_studio', true)
            ->first();

        // Agar nahi mila to check karo ki team owner hai ya nahi
        if (!$userOpenai) {
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $userId)
                ->first();
            
            if ($ownerTeam) {
                $userOpenai = UserOpenai::where('id', $imageId)
                    ->where('team_id', $ownerTeam->id)
                    ->where('is_fashion_studio', true)
                    ->first();
            }
        }

        if (! $userOpenai) {
            return response()->json(['error' => __('Image not found.')], 404);
        }

        $imageData = $request->input('image_data');

        if (Str::startsWith($imageData, 'data:image')) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        }

        $decodedImage = base64_decode($imageData);

        if ($decodedImage === false) {
            return response()->json(['error' => __('Invalid image data.')], 400);
        }

        $extension = 'png';
        $filename = 'cropped_' . Str::random(20) . '_' . time() . '.' . $extension;

        $imageStorage = \App\Helpers\Classes\Helper::settingTwo('ai_image_storage');

        try {
            if ($imageStorage === 'r2') {
                Storage::disk('r2')->put($filename, $decodedImage);
                $newUrl = Storage::disk('r2')->url($filename);
            } elseif ($imageStorage === 's3') {
                Storage::disk('s3')->put($filename, $decodedImage);
                $newUrl = Storage::disk('s3')->url($filename);
            } else {
                Storage::disk('public')->put($filename, $decodedImage);
                $newUrl = '/uploads/' . $filename;
            }

            $croppedImage = $userOpenai->replicate();
            $croppedImage->output = preg_replace('/\s+/', '', $newUrl);
            $croppedImage->created_at = now();
            $croppedImage->updated_at = now();

            $width = $request->input('width');
            $height = $request->input('height');
            $aspectRatio = $request->input('aspect_ratio');

            $existingPayload = $croppedImage->payload;
            if (is_string($existingPayload)) {
                $existingPayload = json_decode($existingPayload, true) ?? [];
            }
            $existingPayload = is_array($existingPayload) ? $existingPayload : [];

            $croppedImage->payload = array_merge($existingPayload, [
                'cropped_width'  => $width,
                'cropped_height' => $height,
                'aspect_ratio'   => $aspectRatio,
            ]);

            $croppedImage->save();

            return response()->json([
                'success'      => true,
                'message'      => __('Image cropped successfully.'),
                'image_id'     => $croppedImage->id,
                'url'          => preg_replace('/\s+/', '', $newUrl),
                'thumbnail'    => ThumbImage(preg_replace('/\s+/', '', $newUrl)),
                'width'        => $width,
                'height'       => $height,
                'aspect_ratio' => $aspectRatio,
            ]);
        } catch (Exception $e) {
            Log::error('Crop image error: ' . $e->getMessage());

            return response()->json(['error' => __('Failed to save cropped image.')], 500);
        }
    }

    public function generate(Request $request): JsonResponse
    {

    
            // ✅ ADD THIS CHECK FIRST:
    $user = Auth::user();
    
    $teamMember = \App\Models\Team\TeamMember::where('user_id', $user->id)
        ->where('status', 'suspended')
        ->first();
    
    if ($teamMember) {
        return response()->json([
            'status'  => 'error',
            'success' => false,
            'message' => __('Your account has been suspended. You cannot generate content.'),
        ], 403);
    }



        try {
            \Log::info('PhotoShootController::generate called', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $request->validate([
                'products'   => 'required|array|min:1|max:3',
                'model'      => 'nullable',
                'pose'       => 'nullable',
                'background' => 'nullable',
            ]);

            $lockKey = $request->lock_key ?? 'photoshoot-' . now()->timestamp . '-' . auth()->id();

            $productUrls = $this->getProductUrls($request->products);
            $modelUrl = $this->getModelUrl($request->model);
            $modelName = $this->getModelName($request->model);
            $backgroundUrl = $this->getBackgroundUrl($request->background);
            $backgroundName = $this->getBackgroundName($request->background);

            $poseUrl = $this->getPoseUrl($request->pose);
            $poseDescription = $this->getPoseDescription($request->pose);
            $isUserPose = $this->isUserPose($request->pose);

            $userSettings = $this->getUserSettings();
            $imageSize = $userSettings->getImageSize();

            $this->generationData = [
                'products'          => $request->products,
                'product_urls'      => $productUrls,
                'model'             => $request->model,
                'model_url'         => $modelUrl,
                'model_name'        => $modelName,
                'pose'              => $request->pose,
                'pose_url'          => $poseUrl,
                'pose_description'  => $poseDescription,
                'is_user_pose'      => $isUserPose,
                'background'        => $request->background,
                'background_url'    => $backgroundUrl,
                'background_name'   => $backgroundName,
                'resolution'        => $userSettings->resolution,
                'ratio'             => $userSettings->ratio,
                'image_width'       => $imageSize['width'],
                'image_height'      => $imageSize['height'],
            ];

            \Log::info('Generation data prepared', ['generationData' => $this->generationData]);

            // IMPORTANT FIX: Return the response from processGeneration directly
           $response = $this->processGeneration($lockKey, $this->generationData);

$data = $response->getData(true);

// Trial User Watermark
// if (
//     isset($data['images'][0]['url']) &&
//    auth()->user()->trial_ends_at &&
//     now()->lessThan(auth()->user()->trial_ends_at)
// ) {

//     $imageUrl = $data['images'][0]['url'];

//     // Original generated image
//     $image = Image::make($imageUrl);

//     // Watermark image
//     $watermark = Image::make(
//         public_path('themes/default/assets/images/trial-watermark.png')
//     );

//     // Resize watermark
//     $watermark->resize(250, null, function ($constraint) {
//         $constraint->aspectRatio();
//     });

//     // Add watermark center
//     $image->insert($watermark, 'center');

//     // Save image
//     $fileName = 'trial_' . time() . '.png';

//     $savePath = public_path('uploads/' . $fileName);

//     $image->save($savePath);

//     // Replace image url
//     $data['images'][0]['url'] = asset('uploads/' . $fileName);
// }

\Log::info('ProcessGeneration response', [
    'status_code' => $response->status(),
    'response_data' => $data
]);

return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error('Generate method error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function getProductUrls(array $productIds): array
    {
        $urls = [];
        $products = Wardrobe::whereIn('id', $productIds)
            ->where('user_id', Auth::id())
            ->get();

        foreach ($products as $product) {
            if ($product->image_url) {
                $urls[] = url($product->image_url);
            }
        }

        return $urls;
    }

    protected function getModelUrl(?string $modelId): ?string
    {
        if (! $modelId) {
            return $this->getRandomStaticModelUrl();
        }

        if (str_starts_with($modelId, 'user-')) {
            $actualId = str_replace('user-', '', $modelId);
            $model = FashionModel::where('id', $actualId)
                ->where('user_id', Auth::id())
                ->first();

            if ($model && $model->image_url) {
                return url($model->image_url);
            }
        }

        return $this->getStaticModelUrl($modelId);
    }

    protected function getModelName(?string $modelId): string
    {
        if (! $modelId) {
            $randomId = $this->getRandomStaticModelId();
            return $this->getStaticModelName($randomId);
        }

        if (str_starts_with($modelId, 'user-')) {
            $actualId = str_replace('user-', '', $modelId);
            $model = FashionModel::where('id', $actualId)
                ->where('user_id', Auth::id())
                ->first();

            if ($model && $model->model_name) {
                return $model->model_name;
            }
        }

        return $this->getStaticModelName($modelId);
    }

    protected function getStaticModelName(string $modelId): string
    {
        $model = DB::table('statick_models')->where('id', $modelId)->first();
        
        if ($model && $model->name) {
            return $model->name;
        }
        
        return 'Professional Model';
    }

    protected function getStaticModelUrl(string $modelId): ?string
    {
        $model = DB::table('statick_models')->where('id', $modelId)->first();
        
        if ($model && $model->image_url) {
            return url($model->image_url);
        }
        
        return null;
    }

    protected function getRandomStaticModelId(): string
    {
        $model = DB::table('statick_models')->inRandomOrder()->first();
        
        if ($model) {
            return (string) $model->id;
        }
        
        return '1';
    }

    protected function getRandomStaticModelUrl(): string
    {
        $randomId = $this->getRandomStaticModelId();
        return $this->getStaticModelUrl($randomId);
    }

    protected function isUserPose(?string $poseId): bool
{
    if (! $poseId) {
        return false;
    }

    // Koi bhi pose select ho — image as reference use karo
    return true;
}

    protected function getPoseUrl(?string $poseId): ?string
{
    if (! $poseId) {
        return null;
    }

    if (str_starts_with($poseId, 'user-')) {
        $actualId = str_replace('user-', '', $poseId);
        $pose = Pose::where('id', $actualId)
            ->where('user_id', Auth::id())
            ->first();

        if ($pose && $pose->image_url) {
            return url($pose->image_url);
        }
        return null;
    }

    // Static pose URL bhi return karo
    $pose = DB::table('static_pose')->where('id', $poseId)->first();
    if ($pose && $pose->image_url) {
        return url($pose->image_url);
    }

    return null;
}

    protected function getPoseDescription(?string $poseId): string
    {
        if (! $poseId) {
            return $this->getRandomStaticPoseDescription();
        }

        if (str_starts_with($poseId, 'user-')) {
            $actualId = str_replace('user-', '', $poseId);
            $pose = Pose::where('id', $actualId)
                ->where('user_id', Auth::id())
                ->first();

            if ($pose && $pose->pose_name) {
                return $pose->pose_name;
            }
        }

        return $this->getStaticPoseDescription($poseId);
    }

    protected function getStaticPoseDescription(string $poseId): string
    {
        $pose = DB::table('static_pose')->where('id', $poseId)->first();
        
        if ($pose && $pose->name) {
            return $pose->name;
        }
        
        return 'Natural professional pose';
    }

    protected function getRandomStaticPoseDescription(): string
    {
        $pose = DB::table('static_pose')->inRandomOrder()->first();
        
        if ($pose && $pose->name) {
            return $pose->name;
        }
        
        return 'Natural professional pose';
    }

    protected function getBackgroundUrl(?string $backgroundId): ?string
    {
        if (! $backgroundId) {
            return $this->getRandomStaticBackgroundUrl();
        }

        if (str_starts_with($backgroundId, 'user-')) {
            $actualId = str_replace('user-', '', $backgroundId);
            $background = Background::where('id', $actualId)
                ->where('user_id', Auth::id())
                ->first();

            if ($background && $background->image_url) {
                return url($background->image_url);
            }
        }

        return $this->getStaticBackgroundUrl($backgroundId);
    }

    protected function getBackgroundName(?string $backgroundId): string
    {
        if (! $backgroundId) {
            $randomId = $this->getRandomStaticBackgroundId();
            return $this->getStaticBackgroundName($randomId);
        }

        if (str_starts_with($backgroundId, 'user-')) {
            $actualId = str_replace('user-', '', $backgroundId);
            $background = Background::where('id', $actualId)
                ->where('user_id', Auth::id())
                ->first();

            if ($background && $background->background_name) {
                return $background->background_name;
            }
        }

        return $this->getStaticBackgroundName($backgroundId);
    }

    protected function getStaticBackgroundName(string $backgroundId): string
    {
        $background = DB::table('static_background')->where('id', $backgroundId)->first();
        
        if ($background && $background->name) {
            return $background->name;
        }
        
        return 'Professional Background';
    }

    protected function getStaticBackgroundUrl(string $backgroundId): ?string
    {
        $background = DB::table('static_background')->where('id', $backgroundId)->first();
        
        if ($background && $background->image_url) {
            return url($background->image_url);
        }
        
        return null;
    }

    protected function getRandomStaticBackgroundId(): string
    {
        $background = DB::table('static_background')->inRandomOrder()->first();
        
        if ($background) {
            return (string) $background->id;
        }
        
        return '1';
    }

    protected function getRandomStaticBackgroundUrl(): string
    {
        $randomId = $this->getRandomStaticBackgroundId();
        return $this->getStaticBackgroundUrl($randomId);
    }

    protected function getGenerationTitle(): string
    {
        return __('Photo Shoot Generation');
    }

    protected function getSlugSuffix(): string
    {
        return 'photo-shoot';
    }

    protected function getPrompt(): string
    {
        $payload = $this->generationData;

        $prompt = 'Generate a photorealistic fashion image. ';

        if (! empty($payload['product_urls'])) {
            $count = count($payload['product_urls']);
            $prompt .= "The model must wear all {$count} provided clothing item" . ($count > 1 ? 's' : '') . '. ';
        }

        if ($payload['is_user_pose']) {
            $prompt .= 'Match the exact pose from the reference image. ';
        } elseif (! empty($payload['pose_description'])) {
            $prompt .= "{$payload['pose_description']}. ";
        }

        $prompt .= 'Requirements: ';
        $prompt .= 'Natural skin texture and body proportions. ';
        $prompt .= 'Realistic fabric draping and clothing fit. ';
        $prompt .= 'Seamless integration of model with background. ';
        $prompt .= 'Professional lighting with natural shadows. ';
        $prompt .= 'High-resolution commercial photography quality. ';
        $prompt .= 'No distortions, artificial artifacts, or unnatural elements.';

        return $prompt;
    }

    protected function getImageUrls(): array
{
    $urls = [];

    if (! empty($this->generationData['model_url'])) {
        $urls[] = $this->generationData['model_url'];
    }

    if (! empty($this->generationData['product_urls'])) {
        $urls = array_merge($urls, $this->generationData['product_urls']);
    }

    
    if (! empty($this->generationData['pose_url'])) {
        $urls[] = $this->generationData['pose_url'];
    }

    if (! empty($this->generationData['background_url'])) {
        $urls[] = $this->generationData['background_url'];
    }

    return $urls;
}

    protected function getResponseKey(): string
    {
        return 'photoshoot';
    }

    protected function getNumImages(): int
    {
        return $this->getUserSettings()->num_images;
    }

    protected function getDemoLimitFeature(): string
    {
        return 'photoshoot';
    }

    public function downloadImage(int $id)
    {
        try {
            $record = UserOpenai::where('is_fashion_studio', true)
                ->findOrFail($id);

            $currentUser = auth()->user();
            $currentUserId = $currentUser->id;

            // 🔥 Check if current user has permission to download
            $canDownload = false;

            // Check team ownership
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $currentUserId)
                ->first();

            $isOwner = (bool) $ownerTeam;
            $teamId = $currentUser->team_id;

            if (!$teamId) {
                if ($ownerTeam) {
                    $teamId = $ownerTeam->id;
                    $isOwner = true;
                }
            }

            if (!$teamId) {
                $memberTeam = DB::table('team_members')
                    ->select('team_id')
                    ->where('user_id', $currentUserId)
                    ->first();
                if ($memberTeam) {
                    $teamId = $memberTeam->team_id;
                }
            }

            if ($isOwner && $teamId && $record->team_id == $teamId) {
                $canDownload = true;
            } elseif ($record->created_by == $currentUserId || $record->user_id == $currentUserId) {
                $canDownload = true;
            }

            if (!$canDownload) {
                abort(403, __('You are not authorized to download this image.'));
            }

            $url = $record->output;

            if (empty($url)) {
                abort(404, __('Image not found.'));
            }

            // Get file content
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'jpg';
            $filename = 'photoshoot_' . $record->id . '_' . time() . '.' . $extension;

            // Check if it's an S3 URL
            $isS3 = str_contains($url, 'digitaloceanspaces.com') || 
                    str_contains($url, env('AWS_URL', '')) || 
                    str_contains($url, '.s3.');

            if ($isS3) {
                // Get file directly from S3
                $key = ltrim($path, '/');
                if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($key)) {
                    $fileContent = \Illuminate\Support\Facades\Storage::disk('s3')->get($key);
                    $mimeType = \Illuminate\Support\Facades\Storage::disk('s3')->mimeType($key);
                } else {
                    abort(404, __('File not found on storage.'));
                }
            } else {
                // Local file
                $localPath = public_path(ltrim($path, '/'));
                if (!file_exists($localPath)) {
                    abort(404, __('File not found.'));
                }
                $fileContent = file_get_contents($localPath);
                $mimeType = mime_content_type($localPath);
            }

            return response($fileContent, 200, [
                'Content-Type' => $mimeType ?: 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($fileContent),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            \Log::error('Download image error: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            abort(500, __('Failed to download image.'));
        }
    }

    /**
     * Download S3 image to local storage and return local URL
     * This avoids CORS issues by serving images from the same domain
     */
    private function getLocalImageCopy(string $s3Url): string
    {
        try {
            // Parse the S3 URL to get the file path
            $parsedUrl = parse_url($s3Url);
            $path = $parsedUrl['path'] ?? '';
            $path = ltrim($path, '/');
            
            // Create a unique local filename based on the S3 path
            $localFilename = 'fashion-studio/' . md5($path) . '_' . basename($path);
            $localPath = public_path('uploads/' . $localFilename);
            
            // If file already exists locally, return the local URL
            if (file_exists($localPath)) {
                return url('uploads/' . $localFilename);
            }
            
            // Ensure directory exists
            if (!file_exists(dirname($localPath))) {
                mkdir(dirname($localPath), 0755, true);
            }
            
            // Download from S3
            if (Storage::disk('s3')->exists($path)) {
                $content = Storage::disk('s3')->get($path);
                file_put_contents($localPath, $content);
                \Log::info('Downloaded S3 image to local', ['s3_path' => $path, 'local_path' => $localPath]);
                return url('uploads/' . $localFilename);
            } else {
                \Log::warning('S3 file not found', ['path' => $path, 'url' => $s3Url]);
                return $s3Url; // Fallback to original URL
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to download S3 image: ' . $e->getMessage(), [
                'url' => $s3Url,
                'error' => $e->getMessage()
            ]);
            return $s3Url; // Fallback to original URL on error
        }
    }

    /**
     * Create thumbnail for S3 image directly on S3 and return thumbnail URL
     */
    private function getLocalThumbnail(string $s3Url): string
    {
        try {
            // Parse the S3 URL to get the file path
            $parsedUrl = parse_url($s3Url);
            $path = $parsedUrl['path'] ?? '';
            $path = ltrim($path, '/');
            
            // Create thumbnail path on S3 (in a "thumbs" folder)
            $thumbFilename = 'thumbs/' . md5($path) . '_' . pathinfo($path, PATHINFO_FILENAME) . '.webp';
            
            // Check if thumbnail already exists on S3
            if (Storage::disk('s3')->exists($thumbFilename)) {
                return Storage::disk('s3')->url($thumbFilename);
            }
            
            // Download original image from S3
            if (!Storage::disk('s3')->exists($path)) {
                \Log::warning('S3 file not found for thumbnail', ['path' => $path, 'url' => $s3Url]);
                return $s3Url; // Fallback to original URL
            }
            
            $imageContent = Storage::disk('s3')->get($path);
            $tempFile = tempnam(sys_get_temp_dir(), 'thumb_') . '.' . pathinfo($path, PATHINFO_EXTENSION);
            file_put_contents($tempFile, $imageContent);
            
            // Create thumbnail using GD
            $thumbData = $this->createThumbnail($tempFile, 800, 800, 80);
            
            // Delete temp file
            @unlink($tempFile);
            
            if ($thumbData) {
                // Upload thumbnail to S3
                Storage::disk('s3')->put($thumbFilename, $thumbData, 'public');
                \Log::info('Thumbnail created on S3', ['original' => $path, 'thumbnail' => $thumbFilename]);
                return Storage::disk('s3')->url($thumbFilename);
            }
            
            return $s3Url; // Fallback to original URL on error
            
        } catch (\Exception $e) {
            \Log::error('Failed to create thumbnail on S3: ' . $e->getMessage(), [
                'url' => $s3Url,
                'error' => $e->getMessage()
            ]);
            return $s3Url; // Fallback to original URL on error
        }
    }
    
    /**
     * Delete thumbnail from S3 when image is deleted
     */
    private function deleteThumbnailFromS3(string $s3Url): void
    {
        try {
            // Parse the S3 URL to get the file path
            $parsedUrl = parse_url($s3Url);
            $path = $parsedUrl['path'] ?? '';
            $path = ltrim($path, '/');
            
            // Create thumbnail path (same as in getLocalThumbnail)
            $thumbFilename = 'thumbs/' . md5($path) . '_' . pathinfo($path, PATHINFO_FILENAME) . '.webp';
            
            // Delete thumbnail from S3 if it exists
            if (Storage::disk('s3')->exists($thumbFilename)) {
                Storage::disk('s3')->delete($thumbFilename);
                \Log::info('Thumbnail deleted from S3', ['thumbnail' => $thumbFilename]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to delete thumbnail from S3: ' . $e->getMessage(), [
                'url' => $s3Url,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create thumbnail from image file using GD
     */
    private function createThumbnail(string $sourceFile, int $maxWidth, int $maxHeight, int $quality): ?string
    {
        try {
            $extension = strtolower(pathinfo($sourceFile, PATHINFO_EXTENSION));
            
            // Create image from file based on extension
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $sourceImage = imagecreatefromjpeg($sourceFile);
                    break;
                case 'png':
                    $sourceImage = imagecreatefrompng($sourceFile);
                    break;
                case 'gif':
                    $sourceImage = imagecreatefromgif($sourceFile);
                    break;
                case 'webp':
                    $sourceImage = imagecreatefromwebp($sourceFile);
                    break;
                default:
                    return null;
            }
            
            if (!$sourceImage) {
                return null;
            }
            
            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);
            
            // Calculate new dimensions
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($extension === 'png' || $extension === 'gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize image
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save thumbnail to buffer
            ob_start();
            imagewebp($thumbnail, null, $quality);
            $thumbData = ob_get_clean();
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
            return $thumbData;
            
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation error: ' . $e->getMessage());
            return null;
        }
    }

    public function proxyImage(Request $request)
{
    $url = $request->query('url');

    if (empty($url)) {
        abort(404, __('File not found.'));
    }

    \Log::info('Proxy image called', ['url' => $url]);

    try {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $path = ltrim($path, '/');

        $content = null;
        $mimeType = null;

        // Check if it's an S3/DigitalOcean Spaces URL
        if (str_contains($url, 'digitaloceanspaces.com') || str_contains($url, '.s3.') || str_contains($url, env('AWS_URL', ''))) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($path)) {
                    $content = \Illuminate\Support\Facades\Storage::disk('s3')->get($path);
                    $mimeType = \Illuminate\Support\Facades\Storage::disk('s3')->mimeType($path);
                    \Log::info('Proxy image: Retrieved from S3', ['path' => $path, 'mime' => $mimeType, 'size' => strlen($content)]);
                } else {
                    \Log::warning('Proxy image: File not found on S3', ['path' => $path, 'url' => $url]);
                    abort(404, __('File not found.'));
                }
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                // Re-throw our own abort(404) so it isn't swallowed by the catch below
                throw $e;
            } catch (\Throwable $s3Exception) {
                // Any S3/network/permission error should surface as 404, not 500
                \Log::error('Proxy image: S3 error while fetching file', [
                    'path'  => $path,
                    'url'   => $url,
                    'error' => $s3Exception->getMessage(),
                ]);
                abort(404, __('File not found.'));
            }
        } else {
            // Local file
            $localPath = public_path($path);
            if (!file_exists($localPath)) {
                abort(404, __('File not found.'));
            }
            $content = file_get_contents($localPath);
            $mimeType = mime_content_type($localPath);
        }

        if ($content === null || empty($content)) {
            abort(404, __('File not found.'));
        }

        return response($content, 200, [
            'Content-Type' => $mimeType ?: 'application/octet-stream',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'public, max-age=86400',
            'Access-Control-Allow-Origin' => '*',
        ]);

    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        // Let 404 (and other intentional HTTP aborts) pass through unchanged
        throw $e;
    } catch (\Throwable $e) {
        \Log::error('Proxy image error: ' . $e->getMessage(), [
            'url' => $url ?? null,
            'error' => $e->getMessage(),
        ]);
        // ✅ Any unexpected error also becomes a 404, never a 500
        abort(404, __('File not found.'));
    }
}

    public function removeImage(Request $request): JsonResponse
{
    $request->validate([
        'image_id' => 'required|integer',
    ]);

    try {
        $record = UserOpenai::where('is_fashion_studio', true)
            ->findOrFail($request->input('image_id'));
        
        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;
        
        // 🔥 Check if current user is team owner or member
        $teamId = $currentUser->team_id;
        $isTeamOwner = false;

        if (!$teamId) {
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $currentUserId)
                ->first();
            if ($ownerTeam) {
                $teamId = $ownerTeam->id;
                $isTeamOwner = true;
            }
        }

        if (!$teamId) {
            $memberTeam = DB::table('team_members')
                ->select('team_id')
                ->where('user_id', $currentUserId)
                ->first();
            if ($memberTeam) {
                $teamId = $memberTeam->team_id;
            }
        }

        if ($teamId && !$isTeamOwner) {
            $ownerCheck = DB::table('teams')
                ->select('id')
                ->where('id', $teamId)
                ->where('user_id', $currentUserId)
                ->first();
            $isTeamOwner = !empty($ownerCheck);
        }
        
        // 🔥 Permission check
        $canDelete = false;
        
        if ($isTeamOwner && $teamId && $record->team_id == $teamId) {
            // Team owner can delete any image from their team
            $canDelete = true;
            \Log::info('Team owner deleted team member image', [
                'team_owner_id' => $currentUserId,
                'image_owner_id' => $record->user_id,
                'image_created_by' => $record->created_by,
                'team_id' => $teamId,
                'image_id' => $record->id
            ]);
        } 
        elseif ($record->created_by == $currentUserId || $record->user_id == $currentUserId) {
            // User can delete their own images
            $canDelete = true;
            \Log::info('User deleted own image', [
                'user_id' => $currentUserId,
                'image_id' => $record->id
            ]);
        }
        
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => __('You are not authorized to delete this image. Only team owner or image creator can delete.'),
            ], 403);
        }
        
        // Delete from DigitalOcean Spaces (S3) and local filesystem
        \deleteFileFromOutputUrl($record->output);
        
        // Also delete thumbnail from S3 if it exists
        $this->deleteThumbnailFromS3($record->output);
        
        // 🔥 Delete the record
        $record->delete();
        
        return response()->json([
            'success' => true,
            'message' => __('Image removed successfully'),
        ]);
        
    } catch (Exception $e) {
        Log::error(__('Failed to remove image'), [
            'id'    => $request->input('image_id'),
            'error' => $e->getMessage(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => __('Failed to remove image: ' . $e->getMessage()),
        ], 500);
    }
}
}

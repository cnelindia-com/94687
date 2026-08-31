<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Jobs\CheckFalAIGenerationJob;
use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Extensions\FashionStudio\System\Services\FashionStudioFalAIService;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\OpenAIGenerator;
use App\Models\Usage;
use App\Models\UserOpenai;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



abstract class BaseFashionStudioController extends Controller
{
    protected const EDIT_MODEL = EntityEnum::NANO_BANANA_PRO_EDIT;

    public function __construct(
        protected FashionStudioFalAIService $falAIService
    ) {}

    abstract protected function getGenerationTitle(): string;
    abstract protected function getSlugSuffix(): string;
    abstract protected function getPrompt(): string;
    abstract protected function getImageUrls(): array;
    abstract protected function getResponseKey(): string;

    /**
     * Source info for GTM events (photoshoot, virtual try-on, etc.).
     *
     * @return array{source: string, source_label: string, credits_label: string, item: string, event_name: string}
     */
    protected function getGtmSourceInfo(): array
    {
        return \App\Services\Analytics\GoogleTagManager::sourceFromAssetType($this->getSlugSuffix());
    }

    protected function getNumImages(): int
    {
        return 1;
    }

    protected function getCreditsPerImage(): int
    {
        return 1;
    }

    protected function getCreditsForCurrentSettings(): int
    {
        $user = Auth::user();
        $plan = $user?->effectiveRelationPlan();

        $weights = [
            '1K'    => is_numeric($plan?->image_1k_weight ?? null) ? (float) $plan->image_1k_weight : 1,
            '2K'    => is_numeric($plan?->image_2k_weight ?? null) ? (float) $plan->image_2k_weight : 2,
            '4K'    => is_numeric($plan?->image_4k_weight ?? null) ? (float) $plan->image_4k_weight : 4,
            'video' => is_numeric($plan?->create_video_weight ?? null) ? (float) $plan->create_video_weight : 5,
        ];

        $settings = $this->getUserSettings();
        $resolution = $settings->resolution;
        $credits = $weights[$resolution] ?? 1;

        return (int) max(1, $credits);
    }

    protected function deductCreditsForRecord(UserOpenai $record, ?int $credits = null): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $creditsToDeduct = $credits ?? $this->getCreditsForCurrentSettings();

        $exists = DB::table('credits')
            ->where('user_id', $user->id)
            ->where('recordid', $record->id)
            ->exists();

        if ($exists) {
            return;
        }

        $gtmSource = $this->getGtmSourceInfo();
        $creditAction = $gtmSource['credits_label'];

        DB::table('credits')->insert([
            'user_id'    => $user->id,
            'credits'    => $creditsToDeduct,
            'types'      => 2,
            'action'     => $creditAction,
            'created_at' => now(),
            'recordid'   => $record->id,
        ]);

        $userId = $user->id;

        DB::update("
            UPDATE users u
            JOIN (
                SELECT
                    user_id,
                    COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
                FROM credits
                WHERE user_id = ?
                GROUP BY user_id
            ) c ON u.id = c.user_id
            SET u.total_credit = c.net_credit
            WHERE u.id = ?
        ", [$userId, $userId]);

        // Only fire when credits are fully used up — not on every generation.
        \App\Services\Analytics\GoogleTagManager::creditsUsedIfExhausted($user, (int) $creditsToDeduct, [
            'record_id'    => $record->id,
            'resolution'   => $this->getUserSettings()->resolution,
            'source'       => $gtmSource['source'],
            'source_label' => $gtmSource['source_label'],
            'action'       => $creditAction,
            'item'         => $gtmSource['item'] ?? 'Image',
        ]);
    }

    protected function refundCreditsForRecord(UserOpenai $record): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $creditsToRefund = (int) max(1, $record->credits ?: $this->getCreditsForCurrentSettings());

        $deductionExists = DB::table('credits')
            ->where('user_id', $user->id)
            ->where('recordid', $record->id)
            ->where('types', 2)
            ->exists();

        $refundExists = DB::table('credits')
            ->where('user_id', $user->id)
            ->where('recordid', $record->id)
            ->where('types', 1)
            ->exists();

        if (! $deductionExists || $refundExists) {
            return;
        }

        DB::table('credits')->insert([
            'user_id'    => $user->id,
            'credits'    => $creditsToRefund,
            'types'      => 1,
            'action'     => 'Refund for failed ' . $this->getGenerationTitle(),
            'created_at' => now(),
            'recordid'   => $record->id,
        ]);

        $userId = $user->id;

        DB::update("
            UPDATE users u
            JOIN (
                SELECT
                    user_id,
                    COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
                FROM credits
                WHERE user_id = ?
                GROUP BY user_id
            ) c ON u.id = c.user_id
            SET u.total_credit = c.net_credit
            WHERE u.id = ?
        ", [$userId, $userId]);
    }

    protected function getUserSettings(): FashionStudioUserSetting
    {
        return FashionStudioUserSetting::getForUser(Auth::id());
    }

    protected function getImageSizeOptions(): array
    {
        $settings = $this->getUserSettings();
        return $settings->getImageSize();
    }

    protected function createRecord(array $payloadData, int $imageIndex = 0, ?int $credits = null): UserOpenai
{
    $user = Auth::user();
    
    // Get user ka team_id
    $teamId = $user?->team_id;
    
    // Agar null hai to check karo ki user team owner hai ya nahi
    if (!$teamId && $user) {
        // Team owner ka users.team_id NULL hota hai, lekin teams table mein entry hoti hai
        $ownerTeam = \Illuminate\Support\Facades\DB::table('teams')
            ->select('id')
            ->where('user_id', $user->id)
            ->first();
        if ($ownerTeam) {
            $teamId = $ownerTeam->id;
        }
    }
    
    // Agar abhi bhi null hai to team_members se lo
    if (!$teamId && $user) {
        $memberTeam = \Illuminate\Support\Facades\DB::table('team_members')
            ->select('team_id')
            ->where('user_id', $user->id)
            ->first();
        if ($memberTeam) {
            $teamId = $memberTeam->team_id;
        }
    }
    
    // Agar abhi bhi null hai to log karo
    if (!$teamId) {
        Log::warning('No team_id found for user', [
            'user_id' => $user?->id,
            'user_name' => $user?->name
        ]);
    }

    $gtmSource = $this->getGtmSourceInfo();
    $payloadData = array_merge($payloadData, [
        'gtm_source'         => $gtmSource['source'],
        'gtm_source_label'   => $gtmSource['source_label'],
        'gtm_credits_label'  => $gtmSource['credits_label'],
        'gtm_item'           => $gtmSource['item'] ?? 'Image',
        'gtm_event_name'     => $gtmSource['event_name'] ?? \App\Services\Analytics\GoogleTagManager::imageEventNameFromItem($gtmSource['item'] ?? 'Image'),
        'slug_suffix'        => $this->getSlugSuffix(),
    ]);

    $record = UserOpenai::create([
        'team_id'    => $teamId,  // <-- FIX
        'title'      => $this->getGenerationTitle() . ($imageIndex > 0 ? " #{$imageIndex}" : ''),
        'slug'       => Str::random(7) . Str::slug($user?->fullName()) . '-' . $this->getSlugSuffix() . ($imageIndex > 0 ? "-{$imageIndex}" : ''),
        'user_id'    => $user?->id,
        'created_by' => Auth::id(),
        'openai_id'  => OpenAIGenerator::where('slug', 'ai_image_generator')->first()?->id,
        'payload'    => $payloadData,
        'input'      => null,
        'response'   => 'FS',
        'output'     => null,
        'hash'       => str()->random(256),
        'credits'    => $credits ?? $this->getCreditsPerImage(),
        'words'      => 0,
        'storage'    => 'public',
        'status'     => ImageStatusEnum::pending->value,
        'model'      => self::EDIT_MODEL,
        'engine'     => self::EDIT_MODEL->engine()->value,
    ]);

    $record->is_fashion_studio = true;
    $record->save();

    return $record;
}

    protected function uploadFile($file): string
    {
        $user = Auth::user();
        $folderPath = 'media/images/u-' . $user?->id;
        // If client uploaded HEIC/HEIF, convert to JPEG for browser compatibility
        try {
            $extension = strtolower($file->getClientOriginalExtension() ?? pathinfo($file->getClientOriginalName() ?? '', PATHINFO_EXTENSION));
        } catch (\Throwable $e) {
            $extension = strtolower(pathinfo($file->getClientName() ?? '', PATHINFO_EXTENSION) ?: '');
        }

        $fileToUpload = $file;

        if (in_array($extension, ['heic', 'heif'], true) && class_exists('\\Imagick')) {
            try {
                $imagick = new \Imagick($file->getRealPath());
                // Normalize orientation and convert
                if (defined('Imagick::ORIENTATION_TOPLEFT')) {
                    $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                }
                $imagick->setImageFormat('jpeg');

                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . (string) Str::uuid() . '.jpg';
                $imagick->writeImage($tmpPath);
                $imagick->clear();
                $imagick->destroy();

                $newName = pathinfo($file->getClientOriginalName() ?? 'image', PATHINFO_FILENAME) . '.jpg';
                $fileToUpload = new UploadedFile($tmpPath, $newName, 'image/jpeg', null, true);
            } catch (\Throwable $e) {
                Log::warning('HEIC conversion failed, falling back to original file', ['error' => $e->getMessage()]);
                $fileToUpload = $file;
            }
        }

        return processSecureFileUpload($fileToUpload, $folderPath);
    }

    /**
     * Normalize a stored path returned by processSecureFileUpload into a publicly accessible URL.
     * Handles both literal public '/uploads/...' files and files stored on the 'public' disk.
     */
    protected function normalizeUploadedPathToUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // If it's already an absolute URL, return as-is
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // If file exists under public path (e.g. public/uploads/...)
        $publicPath = public_path(ltrim($path, '/'));
        if (file_exists($publicPath)) {
            return url($path);
        }

        // If path starts with '/uploads/', try resolving relative to storage 'public' disk
        if (Str::startsWith($path, '/uploads/')) {
            $relative = ltrim(substr($path, strlen('/uploads/')), '/');
            if (Storage::disk('public')->exists($relative)) {
                return Storage::disk('public')->url($relative);
            }
        }

        // If the path looks like a storage relative path (no leading slash), try disk directly
        $relative = ltrim($path, '/');
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        // Fallback: return url(path) and hope it's reachable
        return url($path);
    }

    protected function generateWithAI(UserOpenai $record): void
    {
        try {
            $prompt    = $this->getPrompt();
            $imageUrls = $this->getImageUrls();
            $numImages = $this->getNumImages();
            $imageSize = $this->getImageSizeOptions();

            $requestId = $this->falAIService::generate($prompt, $imageUrls, $numImages, $imageSize);

            $existPayload          = is_array($record->payload) ? $record->payload : [];
            $existPayload['uuid']  = $requestId;

            $record->update([
                'status'  => ImageStatusEnum::processing->value,
                'payload' => $existPayload,
            ]);

            CheckFalAIGenerationJob::dispatch($record->id, 'image')->delay(now()->addSeconds(5));
        } catch (Exception $e) {
            Log::error($this->getGenerationTitle() . ' failed', [
                'record_id' => $record->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            $record->update(['status' => ImageStatusEnum::failed->value]);
        }
    }

    protected function checkWithAI(UserOpenai $record): array
    {
        $payloadData = is_array($record->payload) ? $record->payload : [];
        $uuid        = $payloadData['uuid'] ?? null;
        $response    = $this->falAIService::check($uuid);

            if ($response) {
                $images = data_get($response, 'images', []);

            if (empty($images)) {
                $image  = data_get($response, 'image.url');
                $images = [$image];
            }

            $createdRecords = [];
            $localPath      = $this->downloadAndSaveFile($images[0]);

                if ($localPath) {
                    $record->update([
                        'status' => ImageStatusEnum::completed->value,
                        'output' => $localPath,
                    ]);
                    $this->deductCreditsForRecord($record);
                    $createdRecords[] = $record;

                    for ($i = 1; $i < count($images); $i++) {
                        $additionalLocalPath = $this->downloadAndSaveFile($images[$i]);

                    if (! $additionalLocalPath) {
                        continue;
                    }

                    $duplicateRecord = $this->createRecord($payloadData, $i + 1, (int) $record->credits);
                        $duplicateRecord->update([
                            'status'  => ImageStatusEnum::completed->value,
                            'output'  => $additionalLocalPath,
                            'payload' => $payloadData,
                        ]);
                        $this->deductCreditsForRecord($duplicateRecord);

                        $createdRecords[] = $duplicateRecord;
                    }
                }

            return $createdRecords;
        }

        return [$record];
    }

    protected function downloadAndSaveFile(string $url, ?string $defaultExtension = null): ?string
    {
        try {
            $response = Http::timeout(120)->get($url);

            if ($response->successful()) {
                $extension = $defaultExtension ?? pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $fileName  = Str::uuid() . '.' . $extension;
                $path      = 'media/images/u-' . auth()->id() . '/' . $fileName;

                Storage::disk('public')->put($path, $response->body());

                return Storage::disk('public')->url($path);
            }
        } catch (Exception $e) {
            Log::error('BaseFashionStudioController: Error downloading file', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function addCacheBustingToUrl(string $url, $timestamp): string
    {
        $cacheBuster = is_string($timestamp) ? strtotime($timestamp) : $timestamp;

        if (parse_url($url, PHP_URL_QUERY)) {
            return $url . "&_v={$cacheBuster}";
        }

        return $url . "?_v={$cacheBuster}";
    }

    /**
     * Common status endpoint logic
     */
    public function status(string $id): JsonResponse
    {
        $user = Auth::user();
        
        // Pehle user_id se dhundho (normal case)
        $record = UserOpenai::where('user_id', $user->id)->find($id);
        
        // Agar nahi mila to check karo ki team owner hai ya nahi
        if (!$record) {
            // Team owner ka team find karo
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $user->id)
                ->first();
            
            if ($ownerTeam) {
                // Team owner hai to team_id se record dhundho
                $record = UserOpenai::where('team_id', $ownerTeam->id)
                    ->where('id', $id)
                    ->first();
            }
        }
        
        // Agar abhi bhi nahi mila to error
        if (!$record) {
            abort(404, 'Record not found');
        }

        $response = [
            'status' => $record->status,
        ];

        if ($record->status === ImageStatusEnum::completed->value) {
            $results = [[
                'id'         => $record->id,
                'image_url'  => $record->output,
                'created_at' => $record->created_at->toISOString(),
            ]];

            $payload = is_array($record->payload) ? $record->payload : (json_decode((string) $record->payload, true) ?: []);
            $uuid    = $payload['uuid'] ?? null;

            if ($uuid) {
                $relatedQuery = UserOpenai::where('id', '!=', $record->id)
                    ->where(function ($query) use ($uuid) {
                        $query->where('payload', 'like', '%"uuid":"' . $uuid . '"%')
                            ->orWhere('payload', 'like', '%"uuid": "' . $uuid . '"%');
                    })
                    ->where('status', ImageStatusEnum::completed->value);
                
                // Team owner ke liye: team_id se bhi related records dhundho
                $ownerTeam = DB::table('teams')
                    ->select('id')
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($ownerTeam) {
                    // Owner hai to team_id + user_id dono se dhundho
                    $relatedQuery->where(function($q) use ($user, $ownerTeam) {
                        $q->where('user_id', $user->id)
                          ->orWhere('team_id', $ownerTeam->id);
                    });
                } else {
                    $relatedQuery->where('user_id', $user->id);
                }
                
                $relatedRecords = $relatedQuery->orderBy('id')->get();

                foreach ($relatedRecords as $relatedRecord) {
                    $results[] = [
                        'id'         => $relatedRecord->id,
                        'image_url'  => $record->output,
                        'created_at' => $relatedRecord->created_at->toISOString(),
                    ];
                }
            }

            $response['results'] = $results;

        } elseif ($record->status === ImageStatusEnum::failed->value) {
            $response['message'] = __('Generation failed. Please try again.');
        }

        return response()->json($response)->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Wed, 11 Jan 1984 05:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
        ]);
    }

    public function destroy(string $id): JsonResponse
{
    try {
        $record = UserOpenai::findOrFail($id);

        // Check: Sirf creator hi delete kar sakta hai
        if ($record->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => __('You can only delete images you created.'),
            ], 403);
        }

        // Delete the image file from storage/folder
        \deleteFileFromOutputUrl($record->output);

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => __('Image deleted successfully'),
        ]);

    } catch (Exception $e) {
        Log::error(__('Failed to delete image'), [
            'id'    => $id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => __('Failed to delete image'),
        ], 500);
    }
}

    protected function getDemoLimitFeature(): string
    {
        return 'default';
    }

    protected function getDemoMaxAttempts(): int
    {
        return 3;
    }

    /**
     * Common generate endpoint logic
     */
    protected function processGeneration(string $lockKey, array $payloadData): JsonResponse
    {
        $demoLimitResponse = Helper::checkFashionStudioDemoLimit(
            $this->getDemoLimitFeature(),
            $this->getDemoMaxAttempts()
        );

        if ($demoLimitResponse !== null) {
            return $demoLimitResponse;
        }

        try {
            if (! Cache::lock($lockKey, 10)->get()) {
                return response()->json([
                    'message' => __('Another editing in progress. Please try again later.'),
                ], 409);
            }

            $numImages = $this->getNumImages();

            $driver = Entity::driver(self::EDIT_MODEL)
                ->inputImageCount($numImages)
                ->calculateCredit();

            $user = Auth::user();

            // ✅ Plan null ho toh bhi default weights use karo
            $plan = $user->effectiveRelationPlan();

            $weights = [
                '1K'    => is_numeric($plan?->image_1k_weight ?? null) ? (float) $plan->image_1k_weight : 1,
                '2K'    => is_numeric($plan?->image_2k_weight ?? null) ? (float) $plan->image_2k_weight : 2,
                '4K'    => is_numeric($plan?->image_4k_weight ?? null) ? (float) $plan->image_4k_weight : 4,
                'video' => is_numeric($plan?->create_video_weight ?? null) ? (float) $plan->create_video_weight : 5,
            ];

            $settings      = $this->getUserSettings();
            $resoltionsest = $settings->resolution;

            Log::info('Clearing user activityssss555' . json_encode($settings));

            $neededcredit = ($weights[$resoltionsest] ?? 1) * $numImages;

            Log::info('Clearing user activity' . $neededcredit);

            // ✅ Credits check — plan ho ya na ho
            if ($user->total_credit < $neededcredit) {
                return response()->json(['message' => 'Insufficient credits.'], 402);
            }

            $record = $this->createRecord($payloadData);
            $this->deductCreditsForRecord($record, $neededcredit);
            $this->generateWithAI($record);

            Cache::lock($lockKey)->release();

            return response()->json([
                'id'      => $record->id,
                'success' => true,
                'message' => __($this->getGenerationTitle() . ' started'),
            ]);

        } catch (Exception $e) {
            Log::error($this->getGenerationTitle() . ' failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to start generation: ' . $e->getMessage()),
            ], 500);

        } finally {
            Cache::lock($lockKey)->forceRelease();
        }
    }

    protected function markGenerationFailed(UserOpenai $record): void
    {
        $record->update(['status' => ImageStatusEnum::failed->value]);
        $this->refundCreditsForRecord($record);
    }
}

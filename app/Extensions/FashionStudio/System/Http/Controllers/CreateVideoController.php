<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Jobs\CheckFalAIGenerationJob;
use App\Helpers\Classes\Helper;
use App\Models\OpenAIGenerator;
use App\Models\Team\TeamMember;
use App\Models\Usage;
use App\Models\UserOpenai;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreateVideoController extends BaseFashionStudioController
{
    private array $uploadedPaths = [];

    private string $userPrompt = '';

    public function createVideo(?string $image_id = null): View
    {
        return view('fashion-studio::create-video', [
            'image_id' => $image_id,
        ]);
    }

    public function generateVideo(Request $request): JsonResponse
{
    $request->validate([
        'image'        => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:25600',
        'prompt'       => 'required|string|max:1000',
        'image_width'  => 'nullable|integer|min:1',
        'image_height' => 'nullable|integer|min:1',
        'aspect_ratio' => 'nullable|string',
    ]);

    $lockKey = $request->lock_key ?? 'video-request-' . now()->timestamp . '-' . auth()->id();

    // Upload image
    $imagePath = $this->uploadFile($request->file('image'));

    // Store for later use
    $this->uploadedPaths = ['image' => url($imagePath)];
    $this->userPrompt = $request->get('prompt', '');

    $imageWidth = $request->input('image_width');
    $imageHeight = $request->input('image_height');

    if ($imageWidth && $imageHeight) {
        $aspectRatio = $this->normalizeAspectRatio($this->getAspectRatioFromDimensions((int) $imageWidth, (int) $imageHeight));
        [$videoWidth, $videoHeight] = $this->getVideoDimensions((int) $imageWidth, (int) $imageHeight);
    } else {
        $aspectRatio = '9:16';
        [$videoWidth, $videoHeight] = [1080, 1920];
    }

    return $this->processVideoGeneration($lockKey, [
        'image_path'   => url($imagePath),
        'type'         => 'video',
        'aspect_ratio' => $aspectRatio,
        'resolution'   => '1080p',
        'width'        => $videoWidth,
        'height'       => $videoHeight,
    ]);
}

    /**
     * Check video generation status from database (job updates the record)
     */
    public function videoStatus(string $id): JsonResponse
    {
        $user = Auth::user();
        
        // Pehle user_id se dhundho (normal case)
        $record = UserOpenai::where('user_id', $user->id)->find($id);
        
        // Agar nahi mila to check karo ki team owner hai ya nahi
        if (!$record) {
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $user->id)
                ->first();
            
            if ($ownerTeam) {
                $record = UserOpenai::where('team_id', $ownerTeam->id)
                    ->where('id', $id)
                    ->first();
            }
        }
        
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $response = [
            'status' => $record->status,
        ];

        if ($record->status === ImageStatusEnum::completed->value) {
            $response['results'] = [[
                'id'        => $record->id,
                'video_url' => $record->output,
                'type'      => 'video',
            ]];
        } elseif ($record->status === ImageStatusEnum::failed->value) {
            $response['message'] = __('Video generation failed. Please try again.');
        }

        return response()->json($response);
    }

    /**
     * Process video generation with video-specific model
     */
    protected function processVideoGeneration(string $lockKey, array $payloadData): JsonResponse
{
    if (Helper::appIsDemo()) {
        return response()->json([
            'message' => __('This feature is disabled in demo mode.'),
        ], 422);
    }

    try {
        if (! Cache::lock($lockKey, 10)->get()) {
            return response()->json([
                'message' => __('Another video generation in progress. Please try again later.'),
            ], 409);
        }

        // Plan-based credits check
        $user = Auth::user();
        $plan = $this->resolveActivePlan($user);

        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => __('No active plan found.'),
            ], 403);
        }

        $neededCredit = $plan->create_video_weight ?? 1;

        // Credit balance check
        if ($user->total_credit < $neededCredit) {
            return response()->json([
                'success' => false,
                'message' => __('Insufficient credits. You need :credits credits to generate a video.', [
                    'credits' => $neededCredit,
                ]),
            ], 402);
        }

        $record = $this->createVideoRecord($payloadData);
        $this->deductCreditsForRecord($record, (int) $neededCredit);
        $this->generateVideoWithAI($record, $payloadData);

        Cache::lock($lockKey)->release();

        return response()->json([
            'id'      => $record->id,
            'success' => true,
            'message' => __('Video generation started. Video generation has been queued.'),
        ]);

    } catch (Exception $e) {
        Log::error('Video generation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => __('Failed to start video generation: ' . $e->getMessage()),
        ], 500);

    } finally {
        Cache::lock($lockKey)->forceRelease();
    }
}

    /**
     * Get the video model entity based on setting
     */
    protected function getVideoModelEntity(): EntityEnum
    {
        $modelSetting = setting('fashion-studio-video-default-model', EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value);

        // Try to match the setting to an EntityEnum
        return EntityEnum::fromSlug($modelSetting) ?? EntityEnum::VEO_3_1_IMAGE_TO_VIDEO;
    }

    /**
     * Create database record for video generation
     */
    protected function createVideoRecord(array $payloadData): UserOpenai
    {
        $user = Auth::user();
        $videoModel = $this->getVideoModelEntity();
        
        // Get team_id - owner check karo agar direct team_id nahi hai
        $teamId = $user?->team_id;
        if (!$teamId && $user) {
            $ownerTeam = DB::table('teams')
                ->select('id')
                ->where('user_id', $user->id)
                ->first();
            if ($ownerTeam) {
                $teamId = $ownerTeam->id;
            }
        }
        if (!$teamId && $user) {
            $memberTeam = DB::table('team_members')
                ->select('team_id')
                ->where('user_id', $user->id)
                ->first();
            if ($memberTeam) {
                $teamId = $memberTeam->team_id;
            }
        }

        $record = UserOpenai::create([
            'team_id'   => $teamId,
            'title'     => $this->getGenerationTitle(),
            'slug'      => Str::random(7) . Str::slug($user?->fullName()) . '-' . $this->getSlugSuffix(),
            'user_id'   => $user?->id,
            'openai_id' => OpenAIGenerator::where('slug', 'ai_video')->first()?->id
                ?? OpenAIGenerator::where('slug', 'ai_image_generator')->first()?->id,
            'payload'   => json_encode($payloadData, JSON_THROW_ON_ERROR),
            'input'     => null,
            'response'  => 'FS-VIDEO',
            'output'    => null,
            'hash'      => str()->random(256),
            'credits'   => $payloadData['credits'] ?? $this->getCreditsPerImage(),
            'words'     => 0,
            'storage'   => 'public',
            'status'    => ImageStatusEnum::pending->value,
            'model'     => $videoModel->value,
            'engine'    => $videoModel->engine()->value,
        ]);

        $record->is_fashion_studio = true;
        $record->save();

        return $record;
    }

    protected function resolveActivePlan(?\App\Models\User $user)
    {
        if (! $user) {
            return null;
        }

        $plan = $user->relationPlan;
        if ($plan) {
            return $plan;
        }

        $teamMember = TeamMember::query()
            ->with('team.user')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('role', 'creator')
                    ->orWhere('team_role', 1);
            })
            ->first();

        return $teamMember?->team?->user?->relationPlan;
    }

    /**
     * Generate video with AI
     * ✅ FIXED: Pass aspect_ratio for vertical generation
     */
    protected function generateVideoWithAI(UserOpenai $record, array $payloadData = []): void
    {
        try {
            $prompt = $this->getPrompt();
            $imageUrl = $this->uploadedPaths['image'];
            
            // ✅ Get aspect_ratio from payload data (default to 9:16 for vertical)
            $aspectRatio = $payloadData['aspect_ratio'] ?? '9:16';
            $width = $payloadData['width'] ?? null;
            $height = $payloadData['height'] ?? null;

            Log::info('Video generation started', [
                'record_id' => $record->id,
                'aspect_ratio' => $aspectRatio,
                'width' => $width,
                'height' => $height,
                'image_url' => $imageUrl,
            ]);

            $requestId = $this->falAIService::generateVideo(
                $prompt,
                $imageUrl,
                $aspectRatio,
                $width,
                $height
            );

            $existPayload = $record->payload ? json_decode((string) $record->payload, true) : [];
            $existPayload['uuid'] = $requestId;
            $existPayload['video_model'] = setting('fashion-studio-video-default-model', EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value);
            $existPayload['aspect_ratio'] = $aspectRatio;
            $existPayload['resolution'] = $payloadData['resolution'] ?? '1080p';

            $record->update([
                'status'  => ImageStatusEnum::processing->value,
                'payload' => json_encode($existPayload, JSON_THROW_ON_ERROR),
            ]);

            Log::info('Video generation queued successfully', [
                'record_id' => $record->id,
                'request_id' => $requestId,
            ]);

            // Dispatch job to check video generation status
            CheckFalAIGenerationJob::dispatch($record->id, 'video')->delay(now()->addSeconds(10));
        } catch (Exception $e) {
            Log::error('Video generation failed', [
                'record_id' => $record->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'payload_data' => $payloadData,
            ]);

            $this->markGenerationFailed($record);
        }
    }

    protected function getGenerationTitle(): string
    {
        return __('Create Video Generation');
    }

    protected function getSlugSuffix(): string
    {
        return 'create-video';
    }

    protected function getPrompt(): string
    {
        return $this->userPrompt;
    }

    protected function getImageUrls(): array
    {
        return [$this->uploadedPaths['image'] ?? ''];
    }

    private function getVideoDimensions(int $width, int $height): array
    {
        $ratio = $width / $height;

        if ($width >= $height) {
            $videoWidth = 1920;
            $videoHeight = max(16, (int) round($videoWidth / $ratio));
        } else {
            $videoHeight = 1920;
            $videoWidth = max(16, (int) round($videoHeight * $ratio));
        }

        return [
            $this->roundToMultipleOf16(max(16, $videoWidth)),
            $this->roundToMultipleOf16(max(16, $videoHeight)),
        ];
    }

    private function roundToMultipleOf16(int $value): int
    {
        return max(16, (int) round($value / 16) * 16);
    }

    private function getAspectRatioFromDimensions(int $width, int $height): string
    {
        $gcd = function (int $a, int $b) use (&$gcd): int {
            return $b === 0 ? $a : $gcd($b, $a % $b);
        };

        $divisor = $gcd($width, $height);

        return ($width / $divisor) . ':' . ($height / $divisor);
    }

    private function normalizeAspectRatio(string $aspectRatio): ?string
    {
        $normalized = trim($aspectRatio);

        $supported = [
            '16:9',
            '9:16',
            '4:3',
            '3:4',
            '1:1',
        ];

        if (in_array($normalized, $supported, true)) {
            return $normalized;
        }

        $parts = explode(':', $normalized);
        if (count($parts) !== 2) {
            return null;
        }

        [$width, $height] = array_map('floatval', $parts);
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $ratio = $width / $height;
        $candidates = [
            '16:9' => 16 / 9,
            '9:16' => 9 / 16,
            '4:3'  => 4 / 3,
            '3:4'  => 3 / 4,
            '1:1'  => 1,
        ];

        $closest = null;
        $closestDiff = INF;
        foreach ($candidates as $key => $value) {
            $diff = abs($ratio - $value);
            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closest = $key;
            }
        }

        return $closest;
    }

    protected function getResponseKey(): string
    {
        return 'create_video';
    }
}

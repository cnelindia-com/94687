<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Jobs\CheckFalAIGenerationJob;
use App\Extensions\FashionStudio\System\Models\Background;
use App\Extensions\FashionStudio\System\Services\FashionStudioFalAIService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BackgroundController extends Controller
{
    public function __construct(
        protected FashionStudioFalAIService $falAIService
    ) {}

    protected function deductCreditsForRecord(int $userId, int $recordId, int $credits, string $action): void
    {
        if (DB::table('credits')->where('user_id', $userId)->where('recordid', $recordId)->where('types', 2)->exists()) {
            return;
        }

        DB::table('credits')->insert([
            'user_id' => $userId,
            'credits' => $credits,
            'types' => 2,
            'action' => $action,
            'created_at' => now(),
            'recordid' => $recordId,
        ]);

        DB::update("
            UPDATE users u
            JOIN (
                SELECT user_id,
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

    /**
     * Load user's backgrounds
     */
    public function loadBackgrounds(Request $request): JsonResponse
    {
        $userId = Auth::id();

        // Get user's uploaded backgrounds
        $backgrounds = Background::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->background_name,
                    'image_url'  => $item->image_url,
                    'thumbnail'  => ThumbImage($item->image_url),
                    'category'   => $item->background_category,
                    'exist_type' => $item->exist_type,
                    'status'     => $item->status ?? 'completed',
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success'     => true,
            'backgrounds' => $backgrounds,
        ]);
    }

    /**
     * Upload background to Backgrounds table
     */
    public function uploadBackground(Request $request): JsonResponse
    {
        $request->validate([
            'background_image' => 'required|image|mimes:jpeg,png,jpg|max:25600',
            'background_name'  => 'nullable|string|max:255',
        ]);

        try {
            $userId = Auth::id();
            $file = $request->file('background_image');

            // Store the file
            $url = processSecureFileUpload($file, 'media/images/u-' . $userId);
            $background = Background::create([
                'user_id'             => $userId,
                'background_name'     => $request->input('background_name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'background_type'     => $file->guessExtension() ?? $file->getClientOriginalExtension(),
                'background_category' => $request->input('background_category', 'other'),
                'description'         => '',
                'image_url'           => $url,
                'exist_type'          => 'uploaded',
                'status'              => 'completed',
            ]);

            return response()->json([
                'success'    => true,
                'message'    => __('Background uploaded successfully'),
                'background' => [
                    'id'         => $background->id,
                    'name'       => $background->background_name,
                    'image_url'  => $background->image_url,
                    'thumbnail'  => ThumbImage($background->image_url),
                    'category'   => $background->background_category,
                    'exist_type' => $background->exist_type,
                    'status'     => $background->status,
                    'created_at' => $background->created_at,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to upload background: ' . $e->getMessage()),
            ], 500);
        }
    }

    /**
     * Create AI-generated background
     */
    public function createBackground(Request $request): JsonResponse
    {
        $request->validate([
            'background_description' => 'required|string|min:10|max:1000',
        ]);

        $lockKey = 'background-create-' . Auth::id() . '-' . now()->timestamp;

        try {
            if (! Cache::lock($lockKey, 10)->get()) {
                return response()->json([
                    'message' => __('Another generation in progress. Please try again later.'),
                ], 409);
            }

            $userId = Auth::id();
            
            // Check if user has sufficient credits
            $user = Auth::user();
            if ($user->total_credit < 1) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have enough credits to create a background.'),
                ], 402);
            }
            $description = $request->input('background_description');

            // Enhanced prompt for background generation
            $prompt = "Professional photography background scene: {$description}. High quality, detailed environment, suitable for fashion photography, professional lighting, 4K resolution.";

            // Generate request ID
            $requestId = $this->falAIService::generateFromText($prompt);

            // Create background entry with pending status
            $background = Background::create([
                'user_id'             => $userId,
                'background_name'     => __('AI Generated Background'),
                'background_type'     => 'png',
                'background_category' => 'other',
                'description'         => $description,
                'image_url'           => '/themes/default/assets/img/loading.svg',
                'exist_type'          => 'created',
                'status'              => ImageStatusEnum::processing->value,
                'generation_uuid'     => $requestId,
            ]);

            $this->deductCreditsForRecord($userId, $background->id, 1, 'Background created');

            // Dispatch job to check generation status
            CheckFalAIGenerationJob::dispatch($background->id, 'image', 'background')->delay(now()->addSeconds(5));

            Cache::lock($lockKey)->release();

            return response()->json([
                'success'    => true,
                'message'    => __('Background generation started'),
                'background' => [
                    'id'         => $background->id,
                    'name'       => $background->background_name,
                    'image_url'  => '/themes/default/assets/img/loading.svg',
                    'thumbnail'  => '/themes/default/assets/img/loading.svg',
                    'category'   => $background->background_category,
                    'exist_type' => $background->exist_type,
                    'status'     => $background->status,
                    'created_at' => $background->created_at,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Background creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to create background: ' . $e->getMessage()),
            ], 500);
        } finally {
            Cache::lock($lockKey)->forceRelease();
        }
    }

    /**
     * Check background generation status from database (job updates the record)
     */
    public function checkStatus(string $id): JsonResponse
    {
        $background = Background::where('user_id', Auth::id())->findOrFail($id);

        $response = [
            'success' => true,
            'status'  => strtolower($background->status),
        ];

        if ($background->status === ImageStatusEnum::completed->value) {
            $response['background'] = [
                'id'         => $background->id,
                'name'       => $background->background_name,
                'image_url'  => $background->image_url,
                'thumbnail'  => ThumbImage($background->image_url),
                'category'   => $background->background_category,
                'exist_type' => $background->exist_type,
                'status'     => $background->status,
                'created_at' => $background->created_at,
            ];
        } elseif ($background->status === ImageStatusEnum::failed->value) {
            $response['success'] = false;
            $response['message'] = __('Background generation failed. Please try again.');
        }

        return response()->json($response);
    }

    /**
     * Delete background from Backgrounds table
     */
    public function deleteBackground(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $background = Background::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (! $background) {
                return response()->json([
                    'success' => false,
                    'message' => __('Background not found or unauthorized'),
                ], 404);
            }

            // Delete the background
            $background->delete();

            return response()->json([
                'success' => true,
                'message' => __('Background deleted successfully'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete background: ' . $e->getMessage()),
            ], 500);
        }
    }
}

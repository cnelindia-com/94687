<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Jobs\CheckFalAIGenerationJob;
use App\Extensions\FashionStudio\System\Models\FashionModel;
use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use App\Extensions\FashionStudio\System\Services\FashionStudioFalAIService;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FashionModelController extends Controller
{
    public function __construct(
        protected FashionStudioFalAIService $falAIService
    ) {}

    protected function getUserSettings(): FashionStudioUserSetting
    {
        return FashionStudioUserSetting::getForUser(Auth::id());
    }

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

        $source = \App\Services\Analytics\GoogleTagManager::sourceFromAssetType('fashion_model');
        \App\Services\Analytics\GoogleTagManager::creditsUsedById($userId, $credits, [
            'record_id'    => $recordId,
            'source'       => $source['source'],
            'source_label' => $source['source_label'],
            'action'       => $source['credits_label'],
            'item'         => $source['item'],
        ]);
    }

    /**
     * Load user's models
     */
    public function loadModels(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $fashionModels = FashionModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'             => $item->id,
                    'model_name'     => $item->model_name,
                    'name'           => $item->model_name,
                    'gender'         => $item->model_gender,
                    'model_gender'   => $item->model_gender,
                    'image_url'      => $item->image_url,
                    'thumbnail'      => ThumbImage($item->image_url),
                    'model_category' => $item->model_category,
                    'category'       => $item->model_category,
                    'exist_type'     => $item->exist_type,
                    'status'         => $item->status ?? 'completed',
                    'created_at'     => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'models'  => $fashionModels,
        ]);
    }

    protected function refundCreditsForRecord(int $userId, int $recordId, string $action): void
    {
        $creditsToRefund = (int) DB::table('credits')
            ->where('user_id', $userId)
            ->where('recordid', $recordId)
            ->where('types', 2)
            ->sum('credits');

        $refundExists = DB::table('credits')
            ->where('user_id', $userId)
            ->where('recordid', $recordId)
            ->where('types', 1)
            ->exists();

        if ($creditsToRefund <= 0 || $refundExists) {
            return;
        }

        DB::table('credits')->insert([
            'user_id' => $userId,
            'credits' => $creditsToRefund,
            'types' => 1,
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
     * Upload model to Models table
     */
    public function uploadModel(Request $request): JsonResponse
    {
        $request->validate([
            // NOTE: Laravel's `image` rule may reject HEIC/HEIF on servers without proper support.
            'model_image'    => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:25600',
            'model_name'     => 'required|string|max:255',
            'model_gender'   => 'required|in:Male,Female',
            'model_category' => 'nullable|string|max:255',
        ]);

        try {
            $userId = Auth::id();
            $file   = $request->file('model_image');

            $url          = processSecureFileUpload($file, 'media/images/u-' . $userId);
            $fashionModel = FashionModel::create([
                'user_id'        => $userId,
                'model_name'     => $request->input('model_name'),
                'model_gender'   => $request->input('model_gender'),
                'model_type'     => $file->guessExtension() ?? $file->getClientOriginalExtension(),
                'model_category' => $request->input('model_gender'),
                'description'    => '',
                'image_url'      => $url,
                'exist_type'     => 'uploaded',
                'status'         => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Model uploaded successfully'),
                'model'   => [
                    'id'             => $fashionModel->id,
                    'model_name'     => $fashionModel->model_name,
                    'name'           => $fashionModel->model_name,
                    'gender'         => $fashionModel->model_gender,
                    'image_url'      => $fashionModel->image_url,
                    'thumbnail'      => ThumbImage($fashionModel->image_url),
                    'model_category' => $fashionModel->model_category,
                    'category'       => $fashionModel->model_category,
                    'exist_type'     => $fashionModel->exist_type,
                    'status'         => $fashionModel->status,
                    'created_at'     => $fashionModel->created_at,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to upload model: ' . $e->getMessage()),
            ], 500);
        }
    }

    /**
     * ✅ Credits cut karne ka static helper
     * Dono tables mein update karta hai
     */
    public static function deductCredits(int $userId, int $credits, string $action, string $recordId): void
    {
        // 1. users.total_credit kam karo
        DB::table('users')
            ->where('id', $userId)
            ->decrement('total_credit', $credits);

        // 2. credits table mein record insert karo
        DB::table('credits')->insert([
            'user_id'    => $userId,
            'credits'    => $credits,
            'action'     => $action,
            'recordid'   => $recordId,
            'created_at' => now(),
        ]);
    }

    /**
     * Create AI-generated model
     */
    public function createModel(Request $request): JsonResponse
    {
       
        $request->validate([
            'model_description' => 'required|string|min:10|max:1000',
        ]);

        $lockKey = 'model-create-' . Auth::id() . '-' . now()->timestamp;

        try {
            if (! Cache::lock($lockKey, 10)->get()) {
                return response()->json([
                    'message' => __('Another generation in progress. Please try again later.'),
                ], 409);
            }

            $user = Auth::user();

            // ✅ User settings se resolution lo
            $settings   = FashionStudioUserSetting::getForUser($user->id);
            $resolution = $settings->resolution ?? '1K';

            // ✅ Plan ke weights se credit calculate karo
            $plan = $user->relationPlan; // plan may be null (credits-only users)
            $weights = [
                '1K'    => $plan?->image_1k_weight ?? 1,
                '2K'    => $plan?->image_2k_weight ?? 2,
                '4K'    => $plan?->image_4k_weight ?? 4,
                'video' => $plan?->create_video_weight ?? 5,
            ];

            $neededCredit = $weights[$resolution] ?? 1;

            // ✅ Credit check karo
            if ($user->total_credit < $neededCredit) {
                return response()->json([
                    'success' => false,
                    'message' => __('You do not have enough credits. You need :credits credits.', [
                        'credits' => $neededCredit,
                    ]),
                ], 402);
            }

            $description = $request->input('model_description');

            // Prompt for model generation
            $prompt = "Professional fashion model portrait: model, {$description}. Full body shot, clean background, professional studio lighting, high quality, 4K resolution, fashion photography.";

            // Generate request ID
            $requestId = $this->falAIService::generateFromText($prompt);

            // ✅ FashionModel create karo
            $fashionModel = FashionModel::create([
                'user_id'         => $user->id,
                'model_name'      => __('AI Generated Model'),
                'model_gender'    => 'Unspecified',
                'model_type'      => 'png',
                'model_category'  => 'Unspecified',
                'description'     => $description,
                'image_url'       => '/themes/default/assets/img/loading.svg',
                'exist_type'      => 'created',
                'status'          => ImageStatusEnum::processing->value,
                'generation_uuid' => $requestId,
            ]);

            // ✅ Credits abhi cut karo (model create button click par)
            $this->deductCreditsForRecord($user->id, $fashionModel->id, $neededCredit, 'Credits used for Model creation');

            // ✅ Job dispatch karo — agar fail ho jaye to refund ho jayega
            CheckFalAIGenerationJob::dispatch($fashionModel->id, 'image', 'fashion_model')
                ->delay(now()->addSeconds(5));

            Cache::lock($lockKey)->release();

            return response()->json([
                'success' => true,
                'message' => __('Model generation started'),
                'model'   => [
                    'id'             => $fashionModel->id,
                    'model_name'     => $fashionModel->model_name,
                    'name'           => $fashionModel->model_name,
                    'gender'         => $fashionModel->model_gender,
                    'image_url'      => '/themes/default/assets/img/loading.svg',
                    'thumbnail'      => '/themes/default/assets/img/loading.svg',
                    'model_category' => $fashionModel->model_category,
                    'category'       => $fashionModel->model_category,
                    'exist_type'     => $fashionModel->exist_type,
                    'status'         => $fashionModel->status,
                    'created_at'     => $fashionModel->created_at,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Model creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to create model: ' . $e->getMessage()),
            ], 500);
        } finally {
            Cache::lock($lockKey)->forceRelease();
        }
    }

    /**
     * Check model generation status from database
     */
    public function checkStatus(string $id): JsonResponse
    {
        $fashionModel = FashionModel::where('user_id', Auth::id())->findOrFail($id);

        $response = [
            'success' => true,
            'status'  => strtolower($fashionModel->status),
        ];

        if ($fashionModel->status === ImageStatusEnum::completed->value) {
            $response['model'] = [
                'id'             => $fashionModel->id,
                'model_name'     => $fashionModel->model_name,
                'name'           => $fashionModel->model_name,
                'gender'         => $fashionModel->model_gender,
                'image_url'      => $fashionModel->image_url,
                'thumbnail'      => ThumbImage($fashionModel->image_url),
                'model_category' => $fashionModel->model_category,
                'category'       => $fashionModel->model_category,
                'exist_type'     => $fashionModel->exist_type,
                'status'         => $fashionModel->status,
                'created_at'     => $fashionModel->created_at,
            ];




        } elseif ($fashionModel->status === ImageStatusEnum::failed->value) {
            $response['success'] = false;
            $response['message'] = __('Model generation failed. Please try again.');
        }

        return response()->json($response);
    }

    /**
     * Delete model from Models table
     */
    public function deleteModel(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $fashionModel = FashionModel::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (! $fashionModel) {
                return response()->json([
                    'success' => false,
                    'message' => __('Model not found or unauthorized'),
                ], 404);
            }

            $fashionModel->delete();

            return response()->json([
                'success' => true,
                'message' => __('Model deleted successfully'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete model: ' . $e->getMessage()),
            ], 500);
        }
    }
}

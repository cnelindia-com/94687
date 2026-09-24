<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Jobs\CheckFalAIGenerationJob;
use App\Extensions\FashionStudio\System\Models\Wardrobe;
use App\Extensions\FashionStudio\System\Services\FashionStudioFalAIService;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\Usage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WardrobeController extends Controller
{
    protected const CREATE_MODEL = EntityEnum::NANO_BANANA_PRO;

    public function __construct(
        protected FashionStudioFalAIService $falAIService
    ) {}

    public function index(): View
    {
        return view('fashion-studio::wardrobe');
    }

    /**
     * Load user's wardrobe products
     */
    public function loadWardrobe(Request $request): JsonResponse
    {
        $userId = Auth::id();

        // Get user's uploaded products
        $products = Wardrobe::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $imageUrl = $item->image_url;
                $extension = strtolower(pathinfo($imageUrl ?? '', PATHINFO_EXTENSION));
                return [
                    'id'         => $item->id,
                    'name'       => $item->product_name,
                    'image_url'  => $imageUrl,
                    'thumbnail'  => ThumbImage($imageUrl),
                    'is_heic'    => in_array($extension, ['heic', 'heif'], true),
                    'category'   => $item->product_category,
                    'exist_type' => $item->exist_type,
                    'status'     => $item->status ?? 'completed',
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success'  => true,
            'products' => $products,
        ]);
    }

    /**
     * Upload product to wardrobe
     */
    public function uploadProduct(Request $request): JsonResponse
    {
        $request->validate([
            'product_image' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension() ?? '');
                    $mime = strtolower((string) $value->getMimeType());

                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'heic', 'heif'];
                    $allowedMimes = [
                        'image/jpeg',
                        'image/png',
                        'image/heic',
                        'image/heif',
                        'image/heic-sequence',
                        'image/heif-sequence',
                        'application/octet-stream',
                    ];

                    if (! in_array($extension, $allowedExtensions, true) && ! in_array($mime, $allowedMimes, true)) {
                        $fail(__('The product image field must be a JPG, PNG, HEIC, or HEIF image.'));
                    }
                },
                'max:25600',
            ],
            'product_name'  => 'nullable|string|max:255',
        ]);

        try {
            $userId = Auth::id();
            $file = $request->file('product_image');
            $extension = strtolower($file->getClientOriginalExtension());
            $url = processSecureFileUpload(
                $file,
                'media/images/u-' . $userId
            );

            $product = Wardrobe::create([
                'user_id'          => $userId,
                'product_name'     => $request->input('product_name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'product_type'     => in_array($extension, ['heic', 'heif']) ? 'jpg' : ($file->guessExtension() ?? $extension),
                'product_category' => $request->input('product_category', 'other'),
                'description'      => '',
                'image_url'        => $url,
                'exist_type'       => 'uploaded',
                'status'           => 'completed',
        ]);

        return response()->json([
                'success' => true,
                'message' => __('Product uploaded successfully'),
                'product' => [
                    'id'         => $product->id,
                    'name'       => $product->product_name,
                    'image_url'  => $product->image_url,
                    'thumbnail'  => in_array(strtolower(pathinfo($product->image_url ?? '', PATHINFO_EXTENSION)), ['heic', 'heif'], true)
                        ? asset('vendor/fashion-studio/images/product-modal.png')
                        : ThumbImage($product->image_url),
                    'category'   => $product->product_category,
                    'exist_type' => $product->exist_type,
                    'status'     => $product->status,
                    'created_at' => $product->created_at,
                ],
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => __('Failed to upload product: ' . $e->getMessage()),
        ], 500);
    }
}

    /**
     * Create AI-generated product
     */
    public function createProduct(Request $request): JsonResponse
    {
        $demoLimitResponse = Helper::checkFashionStudioDemoLimit('wardrobe_product', 3);

        if ($demoLimitResponse !== null) {
            return $demoLimitResponse;
        }

        $request->validate([
            'product_description' => 'required|string|min:10|max:1000',
        ]);

        $lockKey = 'product-create-' . Auth::id() . '-' . now()->timestamp;

        try {
            if (! Cache::lock($lockKey, 10)->get()) {
                return response()->json([
                    'message' => __('Another generation in progress. Please try again later.'),
                ], 409);
            }

            $driver = Entity::driver(self::CREATE_MODEL)->inputImageCount(1)->calculateCredit();

            // try {
            //     $driver->redirectIfNoCreditBalance();
            // } catch (Exception $e) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => __('You do not have enough credits to create a product.'),
            //     ], 402);
            // }

            $userId = Auth::id();
            $description = $request->input('product_description');

            // Enhanced prompt for product generation
            $prompt = "Professional product photography of: {$description}. High quality, clean white background, well-lit, commercial photography style, detailed, 4K resolution.";

            // Generate request ID
            $requestId = $this->falAIService::generateFromText($prompt);

            // Create wardrobe entry with pending status
            $product = Wardrobe::create([
                'user_id'          => $userId,
                'product_name'     => __('AI Generated Product'),
                'product_type'     => 'png',
                'product_category' => 'other',
                'description'      => $description,
                'image_url'        => '/themes/default/assets/img/loading.svg',
                'exist_type'       => 'created',
                'status'           => ImageStatusEnum::processing->value,
                'generation_uuid'  => $requestId,
            ]);

            // Dispatch job to check generation status
            CheckFalAIGenerationJob::dispatch($product->id, 'image', 'wardrobe')->delay(now()->addSeconds(5));

            // Track usage
            Usage::getSingle()->updateImageCounts($driver->calculate());
            

            $user = Auth::user();

$exists = DB::table('credits')
    ->where('user_id', $user->id)
    ->where('recordid', $product->id)
    ->exists();

Log::info('Product record check: ' . $product->id);

if (!$exists) {

    $plan = $user->relationPlan; // plan may be null (credits-only users)

    $weights = [
        '1K'    => is_numeric($plan?->image_1k_weight ?? null) ? (float) $plan?->image_1k_weight : 1,
        '2K'    => is_numeric($plan?->image_2k_weight ?? null) ? (float) $plan?->image_2k_weight : 2,
        '4K'    => is_numeric($plan?->image_4k_weight ?? null) ? (float) $plan?->image_4k_weight : 4,
        'video' => is_numeric($plan?->create_video_weight ?? null) ? (float) $plan?->create_video_weight : 5,
    ];

    $resoltionsest = '1K';

    $neededcredit = $weights[$resoltionsest] ?? 1;
    if (! is_numeric($neededcredit)) {
        $neededcredit = floatval(preg_replace('/[^0-9.]/', '', (string) $neededcredit));
    }

    if ($neededcredit <= 0) {
        $neededcredit = 1;
    }

    Log::info('Product credit insert: ' . $product->id);

    $gtmSource = \App\Services\Analytics\GoogleTagManager::sourceFromAssetType('product');

    DB::table('credits')->insert([
        'user_id'    => $user->id,
        'credits'    => $neededcredit,
        'types'      => 2,
        'action'     => $gtmSource['credits_label'],
        'created_at' => now(),
        'recordid'   => $product->id
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

    \App\Services\Analytics\GoogleTagManager::creditsUsedIfExhausted($user, (int) $neededcredit, [
        'record_id'    => $product->id,
        'source'       => $gtmSource['source'],
        'source_label' => $gtmSource['source_label'],
        'action'       => $gtmSource['credits_label'],
        'item'         => $gtmSource['item'],
    ]);

    $resulttests = DB::select("
        SELECT 
            user_id,
            COALESCE(SUM(CASE WHEN types = 1 THEN credits ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN types = 2 THEN credits ELSE 0 END), 0) AS net_credit
        FROM credits
        WHERE user_id = ?
        GROUP BY user_id
    ", [$userId]);

    Log::info('Product credits updated: ' . json_encode($resulttests));
}

            Cache::lock($lockKey)->release();

            return response()->json([
                'success' => true,
                'message' => __('Product generation started'),
                'product' => [
                    'id'         => $product->id,
                    'name'       => $product->product_name,
                    'image_url'  => '/themes/default/assets/img/loading.svg',
                    'thumbnail'  => '/themes/default/assets/img/loading.svg',
                    'category'   => $product->product_category,
                    'exist_type' => $product->exist_type,
                    'status'     => $product->status,
                    'created_at' => $product->created_at,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to create product: ' . $e->getMessage()),
            ], 500);
        } finally {
            Cache::lock($lockKey)->forceRelease();
        }
    }

    /**
     * Check product generation status from database (job updates the record)
     */
    public function checkStatus(string $id): JsonResponse
    {
        $product = Wardrobe::where('user_id', Auth::id())->findOrFail($id);

        $response = [
            'success' => true,
            'status'  => strtolower($product->status),
        ];

        if ($product->status === ImageStatusEnum::completed->value) {
            $response['product'] = [
                'id'         => $product->id,
                'name'       => $product->product_name,
                'image_url'  => $product->image_url,
                'thumbnail'  => ThumbImage($product->image_url),
                'is_heic'    => in_array(strtolower(pathinfo($product->image_url ?? '', PATHINFO_EXTENSION)), ['heic', 'heif'], true),
                'category'   => $product->product_category,
                'exist_type' => $product->exist_type,
                'status'     => $product->status,
                'created_at' => $product->created_at,
            ];
        } elseif ($product->status === ImageStatusEnum::failed->value) {
            $response['success'] = false;
            $response['message'] = __('Product generation failed. Please try again.');
        }

        return response()->json($response);
    }

    /**
     * Delete product from wardrobe
     */
    public function deleteProduct(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $product = Wardrobe::where('id', $id)
                ->where('user_id', $userId)
                ->first();

        if (! $product) {
            return response()->json([
                    'success' => false,
                    'message' => __('Product not found or unauthorized'),
                ], 404);
            }

            // Delete the physical image file first, then the database record
            \deleteFileFromOutputUrl($product->image_url);

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => __('Product deleted successfully'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete product: ' . $e->getMessage()),
            ], 500);
        }
    }
}

<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VirtualTryOnController extends BaseFashionStudioController
{
    private array $uploadedPaths = [];

    public function index(): View
    {
        return view('fashion-studio::virtual-try-on');
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'model_image'   => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:25600',
            'clothes_image' => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:25600',
        ]);

        $lockKey = $request->lock_key ?? 'request-' . now()->timestamp . '-' . auth()->id();

        // Upload images
        $modelPath = $this->uploadFile($request->file('model_image'));
        $clothesPath = $this->uploadFile($request->file('clothes_image'));

        // Store for later use in getImageUrls() — normalize to accessible URLs
        $modelUrl = $this->normalizeUploadedPathToUrl($modelPath) ?? url($modelPath);
        $clothesUrl = $this->normalizeUploadedPathToUrl($clothesPath) ?? url($clothesPath);

        $this->uploadedPaths = [
            'model'   => $modelUrl,
            'clothes' => $clothesUrl,
        ];

        return $this->processGeneration($lockKey, [
            'model_image_path'   => $modelUrl,
            'clothes_image_path' => $clothesUrl,
        ]);
    }

    /**
     * Endpoint for server-side HEIC preview conversion.
     * Accepts a single file and returns a publicly accessible URL (after conversion if needed).
     */
    public function preview(Request $request): JsonResponse
{
    $request->validate([
        'file' => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:25600',
    ]);

    $file = $request->file('file');
    if (! $file) {
        return response()->json(['message' => 'No file provided'], 422);
    }

    $ext = strtolower($file->getClientOriginalExtension());
    $isHeic = in_array($ext, ['heic', 'heif']);

    if ($isHeic && extension_loaded('imagick')) {
        try {
            $imagick = new \Imagick();
            $imagick->readImage($file->getRealPath());
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempPath = $tempDir . '/' . uniqid('heic_') . '.jpg';
            $imagick->writeImage($tempPath);
            $imagick->clear();
            $imagick->destroy();

            // Verify output is actually a valid JPEG, not raw HEIC
            $imageInfo = @getimagesize($tempPath);
            if ($imageInfo === false || $imageInfo['mime'] !== 'image/jpeg') {
                @unlink($tempPath);
                throw new \Exception('Converted file is not a valid JPEG - conversion silently failed');
            }

            $convertedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg',
                'image/jpeg',
                null,
                true
            );

            $path = $this->uploadFile($convertedFile);
            @unlink($tempPath);
        } catch (\Exception $e) {
            \Log::error('HEIC conversion failed for file: ' . $file->getClientOriginalName() . ' | Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'This HEIC file format is not supported for preview. Please try converting it to JPEG on your device first, or upload a different photo.'
            ], 422);
        }
    } else {
        $path = $this->uploadFile($file);
    }

    $url = $this->normalizeUploadedPathToUrl($path) ?? url($path);

    return response()->json(['url' => $url]);
}
    protected function getGenerationTitle(): string
    {
        return __('Virtual Try-On Generation');
    }

    protected function getSlugSuffix(): string
    {
        return 'tryon';
    }

    protected function getPrompt(): string
    {
        return 'Apply the clothes from the second image onto the person in the first image, ensuring a natural and realistic fit. Maintain the original pose and background of the person while seamlessly integrating the clothing.';
    }

    protected function getImageUrls(): array
    {
        return [
            $this->uploadedPaths['model'],
            $this->uploadedPaths['clothes'],
        ];
    }

    protected function getResponseKey(): string
    {
        return 'tryon';
    }

    protected function getDemoLimitFeature(): string
    {
        return 'virtual_tryon';
    }
}

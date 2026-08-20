<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChangeModelController extends BaseFashionStudioController
{
    private array $uploadedPaths = [];

    public function index(): View
    {
        return view('fashion-studio::change-model');
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'original_image'  => 'required|image|max:5120',
            'new_model_image' => 'required|image|max:5120',
        ]);

        $lockKey = $request->lock_key ?? 'request-' . now()->timestamp . '-' . auth()->id();

        // Upload images
        $originalPath = $this->uploadFile($request->file('original_image'));
        $newModelPath = $this->uploadFile($request->file('new_model_image'));

        // Store for later use in getImageUrls()
        $this->uploadedPaths = [
            'original'  => url($originalPath),
            'new_model' => url($newModelPath),
        ];

        return $this->processGeneration($lockKey, [
            'original_image_path'  => url($originalPath),
            'new_model_image_path' => url($newModelPath),
        ]);
    }

    protected function getGenerationTitle(): string
    {
        return __('Change Model Generation');
    }

    protected function getSlugSuffix(): string
    {
        return 'change-model';
    }

    protected function getPrompt(): string
    {
        return 'Edit the original first image to change the model\'s appearance to match the new model in the second image provided, ensuring a natural and realistic integration while maintaining the original background and lighting conditions.';
    }

    protected function getImageUrls(): array
    {
        return [
            $this->uploadedPaths['original'],
            $this->uploadedPaths['new_model'],
        ];
    }

    protected function getResponseKey(): string
    {
        return 'change_model';
    }

    protected function getDemoLimitFeature(): string
    {
        return 'change_model';
    }
}

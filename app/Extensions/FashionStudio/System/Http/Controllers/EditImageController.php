<?php

namespace App\Extensions\FashionStudio\System\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditImageController extends BaseFashionStudioController
{
    private array $uploadedPaths = [];

    private string $userPrompt = '';

    public function index(): View
    {
        return view('fashion-studio::edit-image');
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'image'  => 'required|file|mimes:jpeg,png,jpg,heic,heif|max:5120',
            'prompt' => 'required|string|max:1000',
        ]);

        $lockKey = $request->lock_key ?? 'request-' . now()->timestamp . '-' . auth()->id();

        // Upload image
        $imagePath = $this->uploadFile($request->file('image'));

        // Store for later use in getImageUrls() and getPrompt()
        $this->uploadedPaths = ['image' => url($imagePath)];
        $this->userPrompt = $request->get('prompt');

        return $this->processGeneration($lockKey, [
            'image_path' => url($imagePath),
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
        return $this->userPrompt;
    }

    protected function getImageUrls(): array
    {
        return [$this->uploadedPaths['image']];
    }

    protected function getResponseKey(): string
    {
        return 'change_model';
    }

    protected function getDemoLimitFeature(): string
    {
        return 'edit_image';
    }
}

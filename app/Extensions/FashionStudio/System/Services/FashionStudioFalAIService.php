<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Services;
use App\Extensions\FashionStudio\System\Models\FashionStudioUserSetting;
use Illuminate\Support\Facades\Auth;

use App\Domains\Entity\Enums\EntityEnum;
use App\Helpers\Classes\ApiHelper;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FashionStudioFalAIService
{
    public const GENERATE_ENDPOINT = 'https://queue.fal.run/fal-ai/nano-banana-pro/edit';

    public const TEXT_TO_IMAGE_ENDPOINT = 'https://queue.fal.run/fal-ai/nano-banana-pro';

    public const CHECK_ENDPOINT = 'https://queue.fal.run/fal-ai/nano-banana-pro/requests/%s';

    public const VIDEO_BASE_ENDPOINT = 'https://queue.fal.run/fal-ai/%s';

    public const VIDEO_CHECK_ENDPOINT = 'https://queue.fal.run/fal-ai/%s/requests/%s';

    /**
     * Get the video model endpoint path from setting
     */
    public static function getVideoModel(): string
    {
        return setting('fashion-studio-video-default-model', EntityEnum::VEO_3_1_IMAGE_TO_VIDEO->value);
    }

    /**
     * Get the base model path for checking video status (without sub-paths)
     * e.g., 'veo3.1/image-to-video' -> 'veo3.1'
     * e.g., 'kling-video/v2.1/master/image-to-video' -> 'kling-video'
     */
    public static function getVideoModelBasePath(): string
    {
        $model = self::getVideoModel();
        $parts = explode('/', $model);

        return $parts[0] ?? $model;
    }

    public static function generate($prompt, array $imageUrls = [], int $numImages = 1, array $imageSize = []): string
    {

    
      
    $settingget=FashionStudioUserSetting::getForUser(Auth::id());
  
      
        set_time_limit(0);
        ini_set('max_execution_time', 540);

        $request = [
            'prompt'     => $prompt,
            'image_urls' => $imageUrls,
            'num_images' => $numImages,
             'resolution' =>$settingget->resolution
        ];
          

        if (! empty($imageSize['width']) && ! empty($imageSize['height'])) {
            $request['image_size'] = [
                'width'  => $imageSize['width'],
                'height' => $imageSize['height'],
            ];
        }
      //  $request['image_urls'][1]="https://app.pixelrunway.com/vendor/fashion-studio/images/poses/11.png";

        // print_r($request);
        // die("sdfdfd");

      // Log the exact request being sent to FAL AI
            \Log::info('FashionStudio: Sending request to FAL AI', [
                'endpoint' => self::GENERATE_ENDPOINT,
                'request_payload' => $request,
                'image_size_parameter' => $request['image_size'],
                'note' => 'Check if FAL AI respects image_size parameter'
            ]);
        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->post(self::GENERATE_ENDPOINT, $request);
   // Log the full response from FAL AI
        \Log::info('FashionStudio: FAL AI Response', [
            'status' => $http->status(),
            'response_body' => $http->json(),
            'headers' => $http->headers()
        ]);
        if (($http->status() === 200) && $requestId = $http->json('request_id')) {
            return $requestId;
        }

            
        $detail = $http->json('detail');

        throw new RuntimeException(__($detail ?: 'Check your FAL API key.'));
    }

    /**
     * Generate video from image using configurable model
     * ✅ FIXED: Let API auto-detect aspect ratio from image
     */
    public static function generateVideo(string $prompt, string $imageUrl, ?string $aspectRatio = null, ?int $customWidth = null, ?int $customHeight = null): string
    {
        set_time_limit(0);
        ini_set('max_execution_time', 540);

        $model = self::getVideoModel();
        $url = sprintf(self::VIDEO_BASE_ENDPOINT, $model);

        $request = [
            'prompt'       => $prompt,
            'image_url'    => $imageUrl,
            'mute'         => true,          // Disable audio completely
            'enable_audio' => false,         // Additional audio disable flag
            'generate_audio' => false,       // Prevent audio generation
        ];

        // ✅ Let FAL AI API auto-detect aspect ratio from the input image
        // Only set dimensions if explicitly provided, otherwise let API handle it
        if ($customWidth && $customHeight) {
            $request['width'] = $customWidth;
            $request['height'] = $customHeight;
            $request['video_size'] = $customWidth . 'x' . $customHeight;
        }
        // Note: NOT passing aspect_ratio lets the API auto-detect from the image
    
    // For VEO 3.1 model specifically
    if (str_contains($model, 'veo')) {
        $request['resolution'] = '1080p';
        $request['fps'] = 24;
        $request['mute'] = true;         // Ensure mute for VEO
        $request['enable_audio'] = false;
    }
    
    // For Kling model
    if (str_contains($model, 'kling')) {
        $request['mode'] = 'pro';
        $request['resolution'] = '1080p';
        $request['mute'] = true;         // Ensure mute for Kling
        $request['enable_audio'] = false;
    }

    \Log::info('FashionStudio: Sending video generation request to FAL AI', [
        'model' => $model,
        'aspect_ratio' => $aspectRatio ?? '16:9',
        'width' => $request['width'] ?? null,
        'height' => $request['height'] ?? null,
        'video_size' => $request['video_size'] ?? null,
        'resolution' => $request['resolution'] ?? null,
        'full_request' => $request,
    ]);

    $http = Http::withHeaders([
        'Content-Type'  => 'application/json',
        'Accept'        => 'application/json',
        'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
    ])->post($url, $request);

    \Log::info('FashionStudio: FAL AI Video Response', [
        'status' => $http->status(),
        'response_body' => $http->json(),
    ]);

    if (($http->status() === 200) && $requestId = $http->json('request_id')) {
        return $requestId;
    }

    $detail = $http->json('detail');
    throw new RuntimeException(__($detail ?: 'Check your FAL API key.'));
}

    /**
     * Check video generation status
     */
    public static function checkVideo(string $uuid): ?array
    {
        set_time_limit(0);
        ini_set('max_execution_time', 540);

        $modelBasePath = self::getVideoModelBasePath();
        $url = sprintf(self::VIDEO_CHECK_ENDPOINT, $modelBasePath, $uuid);

        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->get($url);

        // Check if request is still in progress (check this FIRST before treating as error)
        $detail = $http->json('detail');
        $detailString = is_string($detail) ? $detail : (is_array($detail) ? json_encode($detail) : '');
        if ($detailString && str_contains(strtolower($detailString), 'in progress')) {
            return null; // Still processing
        }

        // Handle error responses
        if ($http->status() === 422) {
            $error = is_array($detail) ? ($detail[0]['msg'] ?? json_encode($detail)) : $detail;

            throw new RuntimeException(__($error ?: 'Unable to process request.'));
        }

        // Handle other error status codes (4xx, 5xx)
        if ($http->status() >= 400) {
            $error = is_string($detail) ? $detail : ($http->json('message') ?? $http->json('error') ?? null);

            throw new RuntimeException(__($error ?: 'Video generation failed. Status: ' . $http->status()));
        }

        // Check for error in response body (some APIs return 200 with error in body)
        $error = $http->json('error');
        if ($error) {
            $errorMsg = is_string($error) ? $error : json_encode($error);

            throw new RuntimeException(__($errorMsg));
        }

        // Check for video in the response
        $video = $http->json('video');
        if ($video) {
            $videoUrl = is_array($video) ? data_get($video, 'url') : $video;

            if ($videoUrl) {
                return [
                    'video_url' => $videoUrl,
                    'type'      => 'video',
                ];
            }
        }

        // Some models return videos in different formats
        $videoUrl = $http->json('video_url') ?? $http->json('output.video_url') ?? $http->json('result.video_url');
        if ($videoUrl) {
            return [
                'video_url' => $videoUrl,
                'type'      => 'video',
            ];
        }

        return null;
    }

    /**
     * Generate image from text description
     */
    public static function generateFromText(string $prompt, array $options = []): string
    {
        set_time_limit(0);
        ini_set('max_execution_time', 540);

        $request = array_merge(['prompt' => $prompt], $options);

        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->post(self::TEXT_TO_IMAGE_ENDPOINT, $request);

        if (($http->status() === 200) && $requestId = $http->json('request_id')) {
            return $requestId;
        }

        $detail = $http->json('detail');

        throw new RuntimeException(__($detail ?: 'Failed to generate image. Check your FAL API key.'));
    }

    public static function check($uuid): ?array
    {
        set_time_limit(0);
        ini_set('max_execution_time', 540);

        $url = sprintf(self::CHECK_ENDPOINT, $uuid);

        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->get($url);

        // Check if request is still in progress (check this FIRST before treating as error)
        $detail = $http->json('detail');
        $detailString = is_string($detail) ? $detail : (is_array($detail) ? json_encode($detail) : '');
        if ($detailString && str_contains(strtolower($detailString), 'in progress')) {
            return null; // Still processing
        }

        // Handle error responses
        if ($http->status() === 422) {
            $error = is_array($detail) ? ($detail[0]['msg'] ?? json_encode($detail)) : $detail;

            throw new RuntimeException(__($error ?: 'Unable to process request.'));
        }

        // Handle other error status codes (4xx, 5xx)
        if ($http->status() >= 400) {
            $error = is_string($detail) ? $detail : ($http->json('message') ?? $http->json('error') ?? null);

            throw new RuntimeException(__($error ?: 'Generation failed. Status: ' . $http->status()));
        }

        // Check for error in response body (some APIs return 200 with error in body)
        $error = $http->json('error');
        if ($error) {
            $errorMsg = is_string($error) ? $error : json_encode($error);

            throw new RuntimeException(__($errorMsg));
        }

        // Check if we have images in the response
        $images = $http->json('images');

        if (! $images || ! is_array($images) || count($images) === 0) {
            return null; // No images yet or invalid response
        }

        // Check if images are direct URL strings (new format)
        if (is_string($images[0])) {
            return [
                'images' => $images,
                'size'   => $http->json('size') ?: 'unknown',
            ];
        }

        // Handle object format (old/standard format)
        if (is_array($images[0])) {
            $firstImage = $images[0];
 // Log the actual returned dimensions vs requested
            \Log::warning('FashionStudio: FAL AI Dimension Mismatch', [
                'requested_dimensions' => [
                    'width' => $imageSize['width'] ?? 'not_set',
                    'height' => $imageSize['height'] ?? 'not_set'
                ],
                'returned_dimensions' => [
                    'width' => data_get($firstImage, 'width'),
                    'height' => data_get($firstImage, 'height')
                ],
                'issue' => 'FAL AI ignoring image_size parameter',
                'api_endpoint' => self::GENERATE_ENDPOINT
            ]);
            return [
                'images' => array_map(static function ($image) {
                    return data_get($image, 'url');
                }, $images),
                'size'  => (data_get($firstImage, 'width') ?: 'unknown') . 'x' . (data_get($firstImage, 'height') ?: 'unknown'),
            ];
        }

        // Fallback for unexpected format
        return null;
    }

    /**
     * Calculate aspect ratio from dimensions
     */
    private static function calculateAspectRatio(int $width, int $height): string
    {
        $gcd = function (int $a, int $b) use (&$gcd): int {
            return $b === 0 ? $a : $gcd($b, $a % $b);
        };

        $divisor = $gcd($width, $height);
        $w = $width / $divisor;
        $h = $height / $divisor;

        // Normalize to common aspect ratios
        $ratio = $width / $height;
        if (abs($ratio - 16/9) < 0.1) return '16:9';
        if (abs($ratio - 9/16) < 0.1) return '9:16';
        if (abs($ratio - 4/3) < 0.1) return '4:3';
        if (abs($ratio - 3/4) < 0.1) return '3:4';
        if (abs($ratio - 1) < 0.1) return '1:1';

        return round($w) . ':' . round($h);
    }

    public static function getStatus($url)
    {
        ini_set('max_execution_time', 440);
        set_time_limit(0);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->get($url);

        return $response->json();
    }
}
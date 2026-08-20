<?php

declare(strict_types=1);

namespace App\Extensions\FashionStudio\System\Jobs;

use App\Extensions\FashionStudio\System\Enums\ImageStatusEnum;
use App\Extensions\FashionStudio\System\Models\Background;
use App\Extensions\FashionStudio\System\Models\FashionModel;
use App\Extensions\FashionStudio\System\Models\Pose;
use App\Extensions\FashionStudio\System\Models\Wardrobe;
use App\Extensions\FashionStudio\System\Services\FashionStudioFalAIService;
use App\Models\User;
use App\Models\UserOpenai;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckFalAIGenerationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 60;
    public int $timeout = 600;
    public bool $deleteWhenMissingModels = true;

    private const MODEL_CONFIGS = [
        'user_openai' => [
            'class'      => UserOpenai::class,
            'uuid_field' => 'payload',
            'uuid_key'   => 'uuid',
            'output'     => 'output',
        ],
        'pose' => [
            'class'      => Pose::class,
            'uuid_field' => 'generation_uuid',
            'uuid_key'   => null,
            'output'     => 'image_url',
        ],
        'wardrobe' => [
            'class'      => Wardrobe::class,
            'uuid_field' => 'generation_uuid',
            'uuid_key'   => null,
            'output'     => 'image_url',
        ],
        'fashion_model' => [
            'class'      => FashionModel::class,
            'uuid_field' => 'generation_uuid',
            'uuid_key'   => null,
            'output'     => 'image_url',
        ],
        'background' => [
            'class'      => Background::class,
            'uuid_field' => 'generation_uuid',
            'uuid_key'   => null,
            'output'     => 'image_url',
        ],
    ];

    // Watermark inhi model types pe lagega
    private const WATERMARK_MODELS = ['pose', 'wardrobe', 'fashion_model', 'background', 'user_openai'];

    public function __construct(
        public int $recordId,
        public string $type = 'image',
        public string $modelType = 'user_openai'
    ) {}

    public function backoff(): array
    {
        return [5, 5, 5, 10, 10, 10, 15, 15, 15, 20];
    }

    public function handle(): void
    {
        $record = $this->getRecord();

        if (! $record) {
            Log::warning('CheckFalAIGenerationJob: Record not found', [
                'record_id'  => $this->recordId,
                'model_type' => $this->modelType,
            ]);
            return;
        }

        if (in_array($record->status, [ImageStatusEnum::completed->value, ImageStatusEnum::failed->value], true)) {
            return;
        }

        try {
            $uuid = $this->getUuid($record);

            if (! $uuid) {
                Log::error('CheckFalAIGenerationJob: No UUID found', [
                    'record_id'  => $this->recordId,
                    'model_type' => $this->modelType,
                ]);
                $this->markFailedAndRefund($record);
                return;
            }

            if ($this->type === 'video') {
                $this->checkVideoGeneration($record, $uuid);
            } else {
                $this->checkImageGeneration($record, $uuid);
            }
        } catch (RuntimeException $e) {
            Log::error('CheckFalAIGenerationJob: Fal AI error - marking as failed', [
                'record_id'  => $this->recordId,
                'model_type' => $this->modelType,
                'error'      => $e->getMessage(),
            ]);
            $this->markFailedAndRefund($record);
            $this->delete();
        } catch (Exception $e) {
            Log::error('CheckFalAIGenerationJob: Error checking status', [
                'record_id'  => $this->recordId,
                'model_type' => $this->modelType,
                'error'      => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->markFailedAndRefund($record);
            } else {
                throw $e;
            }
        }
    }

    protected function getRecord(): ?Model
    {
        $config = self::MODEL_CONFIGS[$this->modelType] ?? null;
        if (! $config) {
            return null;
        }
        return $config['class']::find($this->recordId);
    }

    protected function getUuid(Model $record): ?string
    {
        $config = self::MODEL_CONFIGS[$this->modelType] ?? null;
        if (! $config) {
            return null;
        }

        $uuidField = $config['uuid_field'];
        $uuidKey   = $config['uuid_key'];

        if ($uuidKey) {
            $fieldValue = $record->{$uuidField};
            $payload    = is_array($fieldValue)
                ? $fieldValue
                : json_decode((string) $fieldValue, true, 512, JSON_THROW_ON_ERROR);
            return $payload[$uuidKey] ?? null;
        }

        return $record->{$uuidField};
    }

    protected function getOutputField(): string
    {
        $config = self::MODEL_CONFIGS[$this->modelType] ?? null;
        return $config['output'] ?? 'output';
    }

    // =========================================================================
    // VIDEO
    // =========================================================================

    protected function checkVideoGeneration(Model $record, string $uuid): void
    {
        $response = FashionStudioFalAIService::checkVideo($uuid);

        if ($response && isset($response['video_url'])) {
            $localPath = $this->downloadAndSaveFile($response['video_url'], 'mp4', 'video');

            if ($localPath) {
                Log::info('LOCAL PATH GENERATED', ['path' => $localPath, 'record_id' => $record->id]);

                $outputField            = $this->getOutputField();
                $record->{$outputField} = $localPath;
                $record->status         = ImageStatusEnum::completed->value;
                $saved                  = $record->save();

                Log::info('RECORD SAVE RESULT', [
                    'saved'     => $saved,
                    'image_url' => $record->{$outputField},
                    'status'    => $record->status,
                ]);

                $fresh = $record->fresh();
                Log::info('FRESH RECORD DATA', [
                    'image_url' => $fresh->{$outputField},
                    'status'    => $fresh->status,
                ]);

                return;
            }
        }

        $this->releaseOrFail($record);
    }

    // =========================================================================
    // IMAGE
    // =========================================================================

    protected function checkImageGeneration(Model $record, string $uuid): void
    {
        $response = FashionStudioFalAIService::check($uuid);

        if ($response) {
            $images = data_get($response, 'images', []);

            if (empty($images)) {
                $image  = data_get($response, 'image.url');
                $images = $image ? [$image] : [];
            }

            if (! empty($images)) {
                $localPath = $this->downloadAndSaveFile($images[0], null, 'image');

                if ($localPath) {
                    // Trial watermark lagao agar user trial plan pe hai
                    $this->applyTrialWatermarkIfNeeded($record, $localPath);

                    $outputField = $this->getOutputField();
                    $record->update([
                        'status'     => ImageStatusEnum::completed->value,
                        $outputField => $localPath,
                    ]);

                    if ($this->modelType === 'user_openai' && count($images) > 1) {
                        $this->createAdditionalImageRecords($record, array_slice($images, 1));
                    }

                    return;
                }
            }
        }

        $this->releaseOrFail($record);
    }

    // =========================================================================
    // TRIAL WATERMARK LOGIC
    // =========================================================================

    protected function applyTrialWatermarkIfNeeded(Model $record, string $localPath): void
{
    // Sirf inhi model types pe watermark lagega
    if (! in_array($this->modelType, self::WATERMARK_MODELS, true)) {
        Log::info('Watermark skipped - model type not in list', [
            'model_type' => $this->modelType,
            'record_id'  => $record->id,
        ]);
        return;
    }

    $user = User::find($record->user_id);

    if (! $user) {
        Log::warning('CheckFalAIGenerationJob: User not found for watermark check', [
            'user_id' => $record->user_id,
        ]);
        return;
    }

    // Watermark sirf current trial period mein lagana hai. Expired/cancelled
    // paid subscription ke remaining credits par watermark nahi lagna chahiye.
    $trialSubscription = \App\Models\Finance\Subscription::query()
        ->where('user_id', $user->id)
        ->whereRaw('LOWER(stripe_status) = ?', ['trialing'])
        ->whereNotNull('trial_ends_at')
        ->where('trial_ends_at', '>', now())
        ->orderByDesc('id')
        ->first();

    $isTrial = (bool) $trialSubscription;

    Log::info('Trial watermark check details', [
        'user_id'               => $user->id,
        'is_trial'              => $isTrial,
        'trial_subscription_id' => $trialSubscription?->id,
        'trial_ends_at'         => $trialSubscription?->trial_ends_at,
    ]);

    if (! $isTrial) {
        Log::info('Watermark skipped - user is not in an active trial period', [
            'user_id' => $user->id,
        ]);
        return;
    }

    Log::info('CheckFalAIGenerationJob: Trial user — applying watermark', [
        'user_id'    => $user->id,
        'model_type' => $this->modelType,
        'record_id'  => $this->recordId,
        'path'       => $localPath,
    ]);

    $fullDiskPath = public_path(ltrim($localPath, '/'));

    if (! file_exists($fullDiskPath)) {
        Log::error('CheckFalAIGenerationJob: File not found on disk', [
            'path' => $fullDiskPath,
        ]);
        return;
    }

    $ok = $this->stampTrialOnImage($fullDiskPath);

    Log::info('CheckFalAIGenerationJob: Watermark result', [
        'success' => $ok,
        'path'    => $fullDiskPath,
    ]);
}

    /**
     * Trial badge image (trial-watermark.jpg) ko bottom-left mein lagao.
     * Image clear rahegi — koi dark overlay nahi.
     */
   protected function stampTrialOnImage(string $fullPath): bool
{
    if (! function_exists('imagecreatetruecolor')) {
        return false;
    }

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    // ── Step 1: Main image load ──
    $src = match ($ext) {
        'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
        'png'         => @imagecreatefrompng($fullPath),
        'webp'        => @imagecreatefromwebp($fullPath),
        default       => null,
    };

    if (! $src) {
        Log::error('stampTrialOnImage: Failed to load source image', ['path' => $fullPath]);
        return false;
    }

    $imgW = imagesx($src);
    $imgH = imagesy($src);

    // ── Step 2: Canvas with banner ──
    $bannerH = (int) max(50, $imgH * 0.06);
    $canvas = imagecreatetruecolor($imgW, $imgH + $bannerH);
    
    if ($ext === 'png') {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
    }
    
    // Fill canvas with white background for JPEG
    if ($ext !== 'png') {
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $imgW, $imgH + $bannerH, $white);
    }
    
    // Copy original image below the banner
    imagecopy($canvas, $src, 0, $bannerH, 0, 0, $imgW, $imgH);
    imagedestroy($src);

    // ── Step 3: TOP BANNER ──
    $bannerColor = imagecolorallocate($canvas, 55, 30, 100); // Purple
    imagefilledrectangle($canvas, 0, 0, $imgW, $bannerH, $bannerColor);

    $bannerText = 'Upgrade To Paid Plan To Remove Watermarks';
    $fontPath   = $this->findFont();
    $fontSize   = (int) max(14, min($imgW / 30, 18));

    if ($fontPath && function_exists('imagettftext')) {
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $bbox  = imagettfbbox($fontSize, 0, $fontPath, $bannerText);
        $textW = abs($bbox[4] - $bbox[0]);
        $textX = (int) (($imgW - $textW) / 2);
        $textY = (int) (($bannerH / 2) + ($fontSize / 2)-2);
        imagettftext($canvas, $fontSize, 0, $textX, $textY, $white, $fontPath, $bannerText);
    }

    // ── Step 4: Watermark logo load ──
    $logoPath = $this->getWatermarkLogoPath();

    if ($logoPath) {
        $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $logo = match ($logoExt) {
            'png'        => @imagecreatefrompng($logoPath),
            'jpg','jpeg' => @imagecreatefromjpeg($logoPath),
            'webp'       => @imagecreatefromwebp($logoPath),
            default      => null,
        };

        if ($logo) {
            $logoOrigW = imagesx($logo);
            $logoOrigH = imagesy($logo);

            // ✅ Bold dikhne ke liye bada size
            $logoW = (int) max(150, min($imgW * 0.30, 300));
            $logoH = (int) ($logoOrigH * ($logoW / $logoOrigW));

            // Resize
            $logoResized = imagecreatetruecolor($logoW, $logoH);
            imagealphablending($logoResized, false);
            imagesavealpha($logoResized, true);
            $transparent = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
            imagefilledrectangle($logoResized, 0, 0, $logoW, $logoH, $transparent);
            imagecopyresampled($logoResized, $logo, 0, 0, 0, 0, $logoW, $logoH, $logoOrigW, $logoOrigH);
            imagedestroy($logo);

            // ✅ Bold - kam transparent (30 = zyada visible)
            $this->applyLogoOpacity($logoResized, $logoW, $logoH, 30);

            // ✅ Puri image par tile
            imagealphablending($canvas, true);

            $stepX = (int) ($logoW * 1.5);
            $stepY = (int) ($logoH * 1.5);

            for ($y = $bannerH; $y < $imgH; $y += $stepY) {
                for ($x = 0; $x < $imgW; $x += $stepX) {
                    imagecopy($canvas, $logoResized, $x, $y, 0, 0, $logoW, $logoH);
                }
            }

            imagedestroy($logoResized);
        }
    }

    // ── Step 5: Save ──
    $saved = match ($ext) {
        'jpg', 'jpeg' => imagejpeg($canvas, $fullPath, 90),
        'png'         => imagepng($canvas, $fullPath),
        'webp'        => imagewebp($canvas, $fullPath, 90),
        default       => false,
    };

    imagedestroy($canvas);
    return (bool) $saved;
}

    /**
     * Logo opacity adjust karo pixel by pixel.
     * alpha: 0 = fully opaque, 127 = fully transparent
     */
    protected function applyLogoOpacity(\GdImage $image, int $w, int $h, int $alpha): void
    {
        imagealphablending($image, false);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $color     = imagecolorat($image, $x, $y);
                $r         = ($color >> 16) & 0xFF;
                $g         = ($color >> 8) & 0xFF;
                $b         = $color & 0xFF;
                $origAlpha = ($color >> 24) & 0x7F;
                $newAlpha  = min(127, $origAlpha + $alpha);
                imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $r, $g, $b, $newAlpha));
            }
        }

        imagealphablending($image, true);
    }

    /**
     * Logo nahi mila toh simple text fallback
     */
    protected function applyTextFallback(\GdImage $canvas, int $imgW, int $imgH): void
    {
        $text = 'Upgrade To Paid Plan To Remove Watermarks';
        $fontPath = $this->findFont();
        $padding  = (int) max(15, $imgW * 0.02);

        imagealphablending($canvas, true);

        if ($fontPath && function_exists('imagettftext')) {
            $fontSize = (int) max(16, min($imgW / 18, 40));
            $shadow   = imagecolorallocatealpha($canvas, 0, 0, 0, 40);
            $white    = imagecolorallocatealpha($canvas, 255, 255, 255, 30);
            imagettftext($canvas, $fontSize, 0, $padding + 2, $imgH - $padding + 2, $shadow, $fontPath, $text);
            imagettftext($canvas, $fontSize, 0, $padding, $imgH - $padding, $white, $fontPath, $text);
        } else {
            $font  = 5;
            $charH = imagefontheight($font);
            $white = imagecolorallocatealpha($canvas, 255, 255, 255, 30);
            imagestring($canvas, $font, $padding, $imgH - $charH - $padding, $text, $white);
        }
    }

    /**
     * Trial watermark badge path dhundo
     */
    protected function getWatermarkLogoPath(): ?string
    {
        $candidates = [
            public_path('themes/default/assets/images/trial-watermark.png'),
            public_path('themes/default/assets/images/trial-image.png'),
            public_path('themes/default/assets/images/trial-watermark.jpg'),
            public_path('themes/default/assets/images/trial-watermark.webp'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        Log::warning('CheckFalAIGenerationJob: No watermark logo found', [
            'checked_paths' => $candidates,
        ]);

        return null;
    }

    /**
     * Server pe TTF font dhundo.
     */
    protected function findFont(): ?string
    {
        $candidates = [
            public_path('fonts/arial.ttf'),
            public_path('fonts/Arial.ttf'),
            public_path('fonts/roboto.ttf'),
            public_path('fonts/Roboto-Bold.ttf'),
            public_path('fonts/opensans.ttf'),
            public_path('fonts/OpenSans-Bold.ttf'),
            resource_path('fonts/arial.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu-sans-fonts/DejaVuSans-Bold.ttf',
            '/Library/Fonts/Arial Bold.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    // =========================================================================
    // ORIGINAL METHODS
    // =========================================================================

    protected function downloadAndSaveFile(string $url, ?string $defaultExtension = null, string $fileType = 'image'): ?string
    {
        try {
            $response = Http::timeout(120)->get($url);

            if ($response->successful()) {
                $extension = $defaultExtension ?? pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $fileName  = Str::uuid()->toString() . '.' . $extension;
                $folder    = $fileType === 'video' ? 'videos' : 'images';

                $relativePath = 'uploads/media/' . $folder . '/u-' . $this->getUserId();
                $fullPath     = public_path($relativePath);

                if (! file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }

                $filePath = $fullPath . '/' . $fileName;
                file_put_contents($filePath, $response->body());

                if (! file_exists($filePath)) {
                    Log::error('IMAGE NOT SAVED', ['path' => $filePath]);
                    return null;
                }

                $savedUrl = '/' . $relativePath . '/' . $fileName;
                $savedUrl = preg_replace('/\s+/', '', $savedUrl);

                Log::info('IMAGE SAVED SUCCESSFULLY', [
                    'path' => $filePath,
                    'url'  => $savedUrl,
                ]);

                return $savedUrl;
            }
        } catch (Exception $e) {
            Log::error('CheckFalAIGenerationJob: Error downloading file', [
                'url'        => $url,
                'error'      => $e->getMessage(),
                'record_id'  => $this->recordId,
                'model_type' => $this->modelType,
            ]);
        }

        return null;
    }

    protected function getUserId(): ?int
    {
        $record = $this->getRecord();
        return $record?->user_id;
    }

    protected function createAdditionalImageRecords(Model $record, array $additionalImages): void
    {
        foreach ($additionalImages as $index => $imageUrl) {
            $localPath = $this->downloadAndSaveFile($imageUrl);

            if (! $localPath) {
                continue;
            }

            $imageNumber           = $index + 2;
            $newRecord             = $record->replicate();
            $newRecord->title      = $record->title . " #{$imageNumber}";
            $newRecord->slug       = str()->random(7) . '-' . $record->slug . "-{$imageNumber}";
            $newRecord->hash       = str()->random(256);
            $newRecord->status     = ImageStatusEnum::completed->value;
            $newRecord->output     = $localPath;
            $newRecord->created_at = now();
            $newRecord->updated_at = now();
            $newRecord->save();
        }
    }

    protected function releaseOrFail(Model $record): void
    {
        if ($this->attempts() < $this->tries) {
            $this->release($this->getBackoffDelay());
            return;
        }

        $this->markFailedAndRefund($record);
        Log::warning('CheckFalAIGenerationJob: Generation timed out', [
            'record_id'  => $this->recordId,
            'model_type' => $this->modelType,
            'type'       => $this->type,
        ]);
    }

    protected function getBackoffDelay(): int
    {
        $attempt = $this->attempts();

        if ($attempt <= 10) return 5;
        if ($attempt <= 20) return 10;
        if ($attempt <= 40) return 15;

        return 20;
    }

    public function failed(?Exception $exception): void
    {
        $record = $this->getRecord();

        if ($record && $record->status !== ImageStatusEnum::completed->value) {
            $this->markFailedAndRefund($record);
        }

        Log::error('CheckFalAIGenerationJob: Job failed permanently', [
            'record_id'  => $this->recordId,
            'model_type' => $this->modelType,
            'error'      => $exception?->getMessage(),
        ]);
    }

    protected function markFailedAndRefund(Model $record): void
    {
        $record->update(['status' => ImageStatusEnum::failed->value]);

        $userId = $record->user_id;
        $recordId = $record->id;
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
            'user_id'    => $userId,
            'credits'    => $creditsToRefund,
            'types'      => 1,
            'action'     => 'Refund for failed image generation',
            'created_at' => now(),
            'recordid'   => $recordId,
        ]);

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
}

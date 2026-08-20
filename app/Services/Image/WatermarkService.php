<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Helpers\Classes\Helper;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WatermarkService
{
    public function shouldApply(?User $user = null): bool
    {
        if (! function_exists('imagecreatefromstring')) {
            return false;
        }

        if (! setting('trial_watermark_enabled', 1)) {
            return false;
        }

        $user = $user ?? Auth::user();

        if (! $user) {
            return false;
        }

        // Fast path used in some extensions.
        if ((bool) ($user->is_trial_user ?? false) === true) {
            return true;
        }

        // ✅ Users table mein trial_ends_at column check karo
        if ($user->trial_ends_at !== null && now()->lt($user->trial_ends_at)) {
            return true;
        }

        // Subscription-based trial check (Stripe style).
        try {
            $activeSub = Helper::getCurrentActiveSubscription($user->id);
            if (! $activeSub) {
                return false;
            }

            return $activeSub->stripe_status === 'trialing'
                || ($activeSub->trial_ends_at !== null && now()->lt($activeSub->trial_ends_at));
        } catch (\Throwable $e) {
            Log::warning('Watermark: trial check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function apply(string $imageBinary): string
    {
        $path = (string) (setting('trial_watermark_path')
            ?: public_path('themes/default/assets/images/trial-watermark.png'));

        if ($path !== '' && ! is_file($path)) {
            $candidate = public_path(ltrim($path, '/\\'));
            if (is_file($candidate)) {
                $path = $candidate;
            }
        }
        Log::info('apply1');

        if (is_file($path)) {
            $result = $this->applyPngWatermark($imageBinary, $path);
             Log::info('apply2');
            if ($result !== null) {
                 Log::info('apply3');
                return $result;
            }
        }

        return $this->applyTextWatermark($imageBinary);
    }

    private function applyPngWatermark(string $imageBinary, string $watermarkPath): ?string
    {
        Log::info('apply7');
        $image = @imagecreatefromstring($imageBinary);
        if (! $image) {
            return null;
        }

        $mark = @imagecreatefrompng($watermarkPath);
        if (! $mark) {
            imagedestroy($image);
            return null;
        }

        $w  = imagesx($image);
        $h  = imagesy($image);
        $mw = imagesx($mark);
        $mh = imagesy($mark);

        if ($w <= 0 || $h <= 0 || $mw <= 0 || $mh <= 0) {
            imagedestroy($mark);
            imagedestroy($image);
            return null;
        }

        $targetW = max(1, (int) round($w * 0.25));
        $ratio   = $mw / $mh;
        $targetH = max(1, (int) round($targetW / $ratio));

        $scaled = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefilledrectangle($scaled, 0, 0, $targetW, $targetH, $transparent);
        imagecopyresampled($scaled, $mark, 0, 0, 0, 0, $targetW, $targetH, $mw, $mh);
        imagedestroy($mark);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $margin = 20;
        $x      = max(0, $w - $targetW - $margin);
        $y      = max(0, $h - $targetH - $margin);

        imagecopy($image, $scaled, $x, $y, 0, 0, $targetW, $targetH);
        imagedestroy($scaled);

        ob_start();
        imagepng($image);
        $result = (string) ob_get_clean();
        imagedestroy($image);
Log::info('apply8');
        return $result;
    }

    private function applyTextWatermark(string $imageBinary): string
    {
        $image = @imagecreatefromstring($imageBinary);
        if (! $image) {
            return $imageBinary;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        $black = imagecolorallocatealpha($image, 0, 0, 0, 55);
        $white = imagecolorallocatealpha($image, 255, 255, 255, 35);

        $text     = 'TRIAL IMAGE';
        $fontSize = 5;
        $textW    = imagefontwidth($fontSize) * strlen($text);
        $textH    = imagefontheight($fontSize);

        $x = (int) (($width - $textW) / 2);
        $y = (int) (($height - $textH) / 2);

        imagefilledrectangle($image, $x - 10, $y - 10, $x + $textW + 10, $y + $textH + 10, $black);
        imagestring($image, $fontSize, $x + 2, $y + 2, $text, $black);
        imagestring($image, $fontSize, $x, $y, $text, $white);

        ob_start();
        imagepng($image);
        $result = (string) ob_get_clean();
        imagedestroy($image);

        return $result;
    }
}

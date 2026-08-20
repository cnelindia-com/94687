<?php

namespace App\Traits;

use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;

trait HandlesHeicImages
{
    /**
     * Convert HEIC/HEIF to JPEG if needed
     */
    protected function convertHeicToJpeg(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, ['heic', 'heif'])) {
            // For servers with Imagick HEIC support
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new \Imagick();
                    $imagick->readImageBlob(file_get_contents($file->getRealPath()));
                    $imagick->setImageFormat('jpeg');
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageCompressionQuality(90);
                    
                    $tempPath = tempnam(sys_get_temp_dir(), 'heic_conv') . '.jpg';
                    $imagick->writeImage($tempPath);
                    
                    return new UploadedFile(
                        $tempPath,
                        str_replace('.' . $extension, '.jpg', $file->getClientOriginalName()),
                        'image/jpeg',
                        null,
                        true
                    );
                } catch (\Exception $e) {
                    \Log::error('HEIC conversion failed: ' . $e->getMessage());
                }
            }
            
            // Fallback: Store as is and handle preview differently
            return $file;
        }
        
        return $file;
    }
    
    /**
     * Create a thumbnail for HEIC files
     */
    protected function getHeicThumbnail($heicPath)
    {
        // Return a placeholder image URL or generate from HEIC
        return asset('vendor/fashion-studio/images/heic-placeholder.svg');
    }
}
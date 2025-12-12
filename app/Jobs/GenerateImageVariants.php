<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Exception;

class GenerateImageVariants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(): void
    {
        // Get absolute path to the stored image
        $imagePath = Storage::disk('public')->path($this->path);

        if (!file_exists($imagePath)) {
            throw new Exception("Source image not found at: {$imagePath}");
        }

        // Try to load the image
        $image = @imagecreatefromstring(file_get_contents($imagePath));
        if (!$image) {
            throw new Exception("Failed to open image with GD. Make sure it's a valid image file.");
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Define variant sizes
        $variants = [
            'small' => 0.25,
            'medium' => 0.5,
            'large' => 0.75,
        ];

        foreach ($variants as $name => $scale) {
            $newWidth = (int)($width * $scale);
            $newHeight = (int)($height * $scale);

            $variant = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($variant, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $variantPath = 'variants/' . $name . '_' . basename($this->path);

            // Save variant to public storage
            ob_start();
            imagejpeg($variant, null, 90);
            $data = ob_get_clean();

            Storage::disk('public')->put($variantPath, $data);
            imagedestroy($variant);
        }

        imagedestroy($image);
    }
}

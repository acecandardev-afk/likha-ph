<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    /**
     * Ensure a storage directory exists (for products, artisans, payments).
     */
    private function ensureStorageDir(string $dir): void
    {
        $path = storage_path('app/public/' . $dir);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Upload and process product image.
     */
    public function uploadProductImage(UploadedFile $file, int $productId): string
    {
        $this->ensureStorageDir('products');

        $filename = $this->generateFilename('product_' . $productId);

        $image = Image::read($file);

        // Main image (800px wide)
        $image->scale(width: 800);
        $image->toJpeg(quality: 85)->save(
            storage_path('app/public/products/' . $filename)
        );

        // Thumbnail (200px wide)
        $thumbnailFilename = 'thumb_' . $filename;
        $thumbnail = Image::read($file);
        $thumbnail->scale(width: 200);
        $thumbnail->toJpeg(quality: 80)->save(
            storage_path('app/public/products/' . $thumbnailFilename)
        );

        return $filename;
    }

    /**
     * Upload and process artisan profile image.
     */
    public function uploadArtisanImage(UploadedFile $file, int $userId): string
    {
        $this->ensureStorageDir('artisans');

        $filename = $this->generateFilename('artisan_' . $userId);

        $image = Image::read($file);

        // Square crop and resize to 400x400
        $image->cover(400, 400);
        $image->toJpeg(quality: 85)->save(
            storage_path('app/public/artisans/' . $filename)
        );

        return $filename;
    }

    /**
     * Upload and process payment proof image.
     */
    public function uploadPaymentProof(UploadedFile $file, int $orderId): string
    {
        $this->ensureStorageDir('payments');

        $filename = $this->generateFilename('payment_' . $orderId);

        $image = Image::read($file);

        // Resize to max 800px wide
        $image->scale(width: 800);
        $image->toJpeg(quality: 85)->save(
            storage_path('app/public/payments/' . $filename)
        );

        return $filename;
    }

    /**
     * Delete product images.
     */
    public function deleteProductImage(string $filename): bool
    {
        $deleted = Storage::disk('products')->delete($filename);
        
        // Delete thumbnail if exists
        $thumbnailFilename = 'thumb_' . $filename;
        if (Storage::disk('products')->exists($thumbnailFilename)) {
            Storage::disk('products')->delete($thumbnailFilename);
        }

        return $deleted;
    }

    /**
     * Delete artisan profile image.
     */
    public function deleteArtisanImage(string $filename): bool
    {
        return Storage::disk('artisans')->delete($filename);
    }

    /**
     * Delete payment proof image.
     */
    public function deletePaymentProof(string $filename): bool
    {
        return Storage::disk('payments')->delete($filename);
    }

    /**
     * Get product image URL.
     */
    public function getProductImageUrl(string $filename): string
    {
        return Storage::disk('products')->url($filename);
    }

    /**
     * Get product thumbnail URL.
     */
    public function getProductThumbnailUrl(string $filename): string
    {
        $thumbnailFilename = 'thumb_' . $filename;
        
        if (Storage::disk('products')->exists($thumbnailFilename)) {
            return Storage::disk('products')->url($thumbnailFilename);
        }

        return $this->getProductImageUrl($filename);
    }

    /**
     * Get artisan image URL.
     */
    public function getArtisanImageUrl(string $filename): string
    {
        return Storage::disk('artisans')->url($filename);
    }

    /**
     * Get payment proof URL.
     */
    public function getPaymentProofUrl(string $filename): string
    {
        return Storage::disk('payments')->url($filename);
    }

    /**
     * Generate unique filename.
     */
    private function generateFilename(string $prefix): string
    {
        return $prefix . '_' . uniqid() . '_' . time() . '.jpg';
    }

    /**
     * Validate image file.
     */
    public function validateImage(UploadedFile $file, int $maxSizeKb = 2048): array
    {
        $errors = [];

        // Check file type
        if (!in_array($file->extension(), ['jpg', 'jpeg', 'png'])) {
            $errors[] = 'Image must be JPG, JPEG, or PNG format.';
        }

        // Check file size
        if ($file->getSize() > ($maxSizeKb * 1024)) {
            $errors[] = "Image must be less than {$maxSizeKb}KB.";
        }

        // Check if it's actually an image
        try {
            $image = Image::read($file);
            
            // Check minimum dimensions (optional)
            if ($image->width() < 400 || $image->height() < 400) {
                $errors[] = 'Image must be at least 400x400 pixels.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file.';
        }

        return $errors;
    }
}
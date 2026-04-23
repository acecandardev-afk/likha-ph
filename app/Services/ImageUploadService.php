<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use App\Support\PublicMediaUrl;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    /**
     * Write processed JPEG to the given disk. Uses Storage so uploads can target S3/R2 in production.
     */
    public function putJpegToDisk(
        string $disk,
        string $filename,
        $image,
        int $quality = 85
    ): void {
        if ($this->isLocalDisk($disk) && is_string($root = (string) config("filesystems.disks.{$disk}.root", '')) && $root !== '') {
            if (! File::isDirectory($root)) {
                File::makeDirectory($root, 0755, true);
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'likha_img_');
        try {
            $image->toJpeg(quality: $quality)->save($tmp);
            $content = (string) file_get_contents($tmp);
            $options = ['visibility' => 'public'];
            Storage::disk($disk)->put($filename, $content, $options);
        } finally {
            if (is_string($tmp) && file_exists($tmp)) {
                @unlink($tmp);
            }
        }
    }

    private function isLocalDisk(string $disk): bool
    {
        return (string) config("filesystems.disks.{$disk}.driver", 'local') === 'local';
    }

    private function ensureStorageDir(string $dir): void
    {
        $path = storage_path('app/public/'.$dir);
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Upload and process product image.
     */
    public function uploadProductImage(UploadedFile $file, int $productId): string
    {
        if ($this->isLocalDisk('products')) {
            $this->ensureStorageDir('products');
        }

        $filename = $this->generateFilename('product_'.$productId);

        $image = Image::read($file);

        $image->scale(width: 800);
        $this->putJpegToDisk('products', $filename, $image, 85);

        $thumbnailFilename = 'thumb_'.$filename;
        $thumbnail = Image::read($file);
        $thumbnail->scale(width: 200);
        $this->putJpegToDisk('products', $thumbnailFilename, $thumbnail, 80);

        return $filename;
    }

    /**
     * Upload and process artisan profile image.
     */
    public function uploadArtisanImage(UploadedFile $file, int $userId): string
    {
        if ($this->isLocalDisk('artisans')) {
            $this->ensureStorageDir('artisans');
        }

        $filename = $this->generateFilename('artisan_'.$userId);

        $image = Image::read($file);

        $image->cover(400, 400);
        $this->putJpegToDisk('artisans', $filename, $image, 85);

        return $filename;
    }

    /**
     * Upload and process payment proof image.
     */
    public function uploadPaymentProof(UploadedFile $file, int $orderId): string
    {
        if ($this->isLocalDisk('payments')) {
            $this->ensureStorageDir('payments');
        }

        $filename = $this->generateFilename('payment_'.$orderId);

        $image = Image::read($file);

        $image->scale(width: 800);
        $this->putJpegToDisk('payments', $filename, $image, 85);

        return $filename;
    }

    /**
     * Delete product images.
     */
    public function deleteProductImage(string $filename): bool
    {
        $deleted = Storage::disk('products')->delete($filename);

        $thumbnailFilename = 'thumb_'.$filename;
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
        return PublicMediaUrl::url('products', $filename);
    }

    /**
     * Get product thumbnail URL.
     */
    public function getProductThumbnailUrl(string $filename): string
    {
        $thumbnailFilename = 'thumb_'.$filename;

        if (Storage::disk('products')->exists($thumbnailFilename)) {
            return PublicMediaUrl::url('products', $thumbnailFilename);
        }

        return $this->getProductImageUrl($filename);
    }

    /**
     * Get artisan image URL.
     */
    public function getArtisanImageUrl(string $filename): string
    {
        return PublicMediaUrl::url('artisans', $filename);
    }

    /**
     * Get payment proof URL.
     */
    public function getPaymentProofUrl(string $filename): string
    {
        return PublicMediaUrl::url('payments', $filename);
    }

    /**
     * Generate unique filename.
     */
    private function generateFilename(string $prefix): string
    {
        return $prefix.'_'.uniqid().'_'.time().'.jpg';
    }

    /**
     * Validate image file.
     */
    public function validateImage(UploadedFile $file, int $maxSizeKb = 2048): array
    {
        $errors = [];

        if (! in_array($file->extension(), ['jpg', 'jpeg', 'png'])) {
            $errors[] = 'Image must be JPG, JPEG, or PNG format.';
        }

        if ($file->getSize() > ($maxSizeKb * 1024)) {
            $errors[] = "Image must be less than {$maxSizeKb}KB.";
        }

        try {
            $image = Image::read($file);

            if ($image->width() < 400 || $image->height() < 400) {
                $errors[] = 'Image must be at least 400x400 pixels.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file.';
        }

        return $errors;
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicMediaUrl
{
    /**
     * Public URL for a path on a named disk.
     *
     * Local disks use {@see asset()} so URLs honor APP_URL (subdirectory / XAMPP installs).
     * Root-relative "/storage/..." breaks when the app is not served from the web root.
     * S3-compatible disks use Storage::url (typically absolute).
     */
    public static function url(string $disk, string $path): string
    {
        $path = ltrim($path, '/');
        if ($path === '') {
            return '';
        }

        if ((string) config("filesystems.disks.{$disk}.driver", 'local') !== 's3') {
            return match ($disk) {
                'products' => asset('storage/products/'.$path),
                'artisans' => asset('storage/artisans/'.$path),
                'payments' => asset('storage/payments/'.$path),
                'delivery_proofs' => asset('storage/delivery-proofs/'.$path),
                'public' => asset('storage/'.$path),
                default => Storage::disk($disk)->url($path),
            };
        }

        return Storage::disk($disk)->url($path);
    }
}

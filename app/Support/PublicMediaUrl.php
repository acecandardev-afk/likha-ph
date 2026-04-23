<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicMediaUrl
{
    /**
     * Public URL for a path on a named disk.
     *
     * Local disks use root-relative /storage/... so the browser resolves against the
     * current host (avoids broken images when APP_URL / config cache does not match www).
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
                'products' => '/storage/products/'.$path,
                'artisans' => '/storage/artisans/'.$path,
                'payments' => '/storage/payments/'.$path,
                'public' => '/storage/'.$path,
                default => Storage::disk($disk)->url($path),
            };
        }

        return Storage::disk($disk)->url($path);
    }
}

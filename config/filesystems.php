<?php

$mediaUseS3 = env('MEDIA_DRIVER', 'local') === 's3' && ! empty(env('AWS_BUCKET'));
$mediaPrefix = trim((string) env('MEDIA_S3_PATH_PREFIX', ''), '/');
$objectRoot = function (string $dir) use ($mediaPrefix) {
    $dir = trim($dir, '/');
    if ($mediaPrefix === '') {
        return $dir;
    }
    return $mediaPrefix.'/'.$dir;
};

$mediaS3Base = [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
];

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // Custom disk for product images (local) or S3 / R2 when MEDIA_DRIVER=s3
        'products' => $mediaUseS3
            ? array_merge($mediaS3Base, [
                'root' => $objectRoot('products'),
                'visibility' => 'public',
                'throw' => false,
            ])
            : [
                'driver' => 'local',
                'root' => storage_path('app/public/products'),
                'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/products',
                'visibility' => 'public',
                'throw' => false,
            ],

        'artisans' => $mediaUseS3
            ? array_merge($mediaS3Base, [
                'root' => $objectRoot('artisans'),
                'visibility' => 'public',
                'throw' => false,
            ])
            : [
                'driver' => 'local',
                'root' => storage_path('app/public/artisans'),
                'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/artisans',
                'visibility' => 'public',
                'throw' => false,
            ],

        'payments' => $mediaUseS3
            ? array_merge($mediaS3Base, [
                'root' => $objectRoot('payments'),
                'visibility' => 'public',
                'throw' => false,
            ])
            : [
                'driver' => 'local',
                'root' => storage_path('app/public/payments'),
                'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/payments',
                'visibility' => 'public',
                'throw' => false,
            ],

        'delivery_proofs' => $mediaUseS3
            ? array_merge($mediaS3Base, [
                'root' => $objectRoot('delivery-proofs'),
                'visibility' => 'public',
                'throw' => false,
            ])
            : [
                'driver' => 'local',
                'root' => storage_path('app/public/delivery-proofs'),
                'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/delivery-proofs',
                'visibility' => 'public',
                'throw' => false,
            ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

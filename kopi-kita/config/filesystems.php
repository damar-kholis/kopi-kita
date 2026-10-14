<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // foto menu disimpan langsung di public/uploads (tanpa storage:link)
        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL', 'http://localhost').'/uploads',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL', 'http://localhost').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    // dibuat oleh: php artisan storage:link
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

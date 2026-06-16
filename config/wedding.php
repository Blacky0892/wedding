<?php

return [
    'uploads_enabled' => env('WEDDING_UPLOADS_ENABLED', true),
    'max_file_size_mb' => (int) env('WEDDING_MAX_FILE_SIZE_MB', 300),
    'max_files_per_request' => (int) env('WEDDING_MAX_FILES_PER_REQUEST', 10),
    'throttle' => env('WEDDING_UPLOAD_THROTTLE', '20,1'),
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'mp4',
        'mov',
        'webm',
    ],
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'video/mp4',
        'video/quicktime',
        'video/webm',
    ],

    'yandex_disk' => [
        'token' => env('YANDEX_DISK_TOKEN'),
        'base_path' => env('YANDEX_DISK_BASE_PATH', '/WeddingPhotos'),
        'originals_path' => env('YANDEX_DISK_ORIGINALS_PATH', '/WeddingPhotos/originals'),
        'thumbs_path' => env('YANDEX_DISK_THUMBS_PATH', '/WeddingPhotos/thumbs'),
        'api_base_url' => env('YANDEX_DISK_API_BASE_URL', 'https://cloud-api.yandex.net/v1/disk'),
    ],
];

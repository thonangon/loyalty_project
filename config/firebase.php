<?php

return [
    'credentials' => [
        'file' => storage_path(env('FIREBASE_CREDENTIALS')),
    ],
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
];
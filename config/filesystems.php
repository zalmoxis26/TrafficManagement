<?php

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
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),  // Solo para servicios compatibles con S3 (no AWS)
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,  // Lanza excepciones en errores (true para desarrollo)
            'visibility' => 'private',  // o 'public' para acceso directo
            'options' => [
                'StorageClass' => 'STANDARD_IA',  # Clase de almacenamiento
            ],
        ],

       
        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => (int)env('FTP_PORT', 21),
            'root' => '',
            'passive' => true,
            'ssl' => false,
            'timeout' => 30,
        ],


        'gcs' => [
        'driver' => 'gcs',
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'adp-expediente-pedimentos-sai'),
        'key_file' => env('GOOGLE_CLOUD_KEY_FILE', null), // Ruta al JSON de credenciales
        'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'mi-app-expediente-pedimento-sai'),
        'path_prefix' => '', // Asegúrate de que está vacío para evitar duplicación
        'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', null), // Opcional
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

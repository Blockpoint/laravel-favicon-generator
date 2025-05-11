<?php

// config for Blockpoint/LaravelFaviconGenerator
return [
    /*
    |--------------------------------------------------------------------------
    | Favicon Output Path
    |--------------------------------------------------------------------------
    |
    | This value determines the path where the generated favicons will be stored
    | relative to the public directory.
    |
    */
    'output_path' => 'favicon',

    /*
    |--------------------------------------------------------------------------
    | Favicon Types
    |--------------------------------------------------------------------------
    |
    | This array defines the different favicon types that will be generated.
    | Each type has its own specifications.
    |
    */
    'favicon_types' => [
        'ico' => [
            'filename' => 'favicon.ico',
            'sizes' => [16, 32, 48],
            'quality' => 100, // PNG quality (0-100) for the source images used to create the ICO
        ],
        'png' => [
            'filename' => 'favicon-96x96.png',
            'size' => 96,
            'quality' => 100, // PNG quality (0-100), higher is better quality
        ],
        'svg' => [
            'filename' => 'favicon.svg',
        ],
        'apple_touch_icon' => [
            'filename' => 'apple-touch-icon.png',
            'size' => 180,
            'quality' => 100, // PNG quality (0-100), higher is better quality
        ],
        'web_app_manifest_icons' => [
            'sizes' => [192, 512],
            'filename_pattern' => 'web-app-manifest-{size}x{size}.png',
            'quality' => 100, // PNG quality (0-100), higher is better quality
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web App Manifest
    |--------------------------------------------------------------------------
    |
    | Configuration for the site.webmanifest file.
    |
    */
    'web_manifest' => [
        'filename' => 'site.webmanifest',
        'content' => [
            'name' => '',
            'short_name' => '',
            'icons' => [],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ],
    ],
];

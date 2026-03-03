<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Output Path
    |--------------------------------------------------------------------------
    |
    | This option defines the output path for the generated manifest.json file.
    | The path is relative to the public/ directory. By default, it will be
    | generated at public/manifest.json.
    |
    */

    'manifest_path' => env('PWA_MANIFEST_PATH', 'manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Path
    |--------------------------------------------------------------------------
    |
    | This option defines the output path for the service worker file, relative
    | to the public/ directory. It must match the path registered in the body
    | component and the scope it is intended to control.
    |
    */

    'sw_path' => env('PWA_SW_PATH', 'sw.js'),

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest
    |--------------------------------------------------------------------------
    |
    | This section defines the settings for the Progressive Web Application
    | (PWA) manifest. The manifest provides metadata about the application,
    | such as its name, icons, and theme colors, which are used when the
    | application is installed on a user's device.
    |
    */

    'manifest' => [
        'id' => env('PWA_ID', '/'),
        'name' => env('APP_NAME', 'stry'),
        'short_name' => env('PWA_SHORT_NAME', 'stry'),
        'description' => env('PWA_DESCRIPTION', 'A streaming service for videos, movies and TV shows.'),
        'start_url' => env('PWA_START_URL', '/'),
        'scope' => env('PWA_SCOPE', '/'),
        'display_override' => ['fullscreen', 'standalone'],
        'display' => env('PWA_DISPLAY', 'fullscreen'),
        'orientation' => env('PWA_ORIENTATION', 'any'),
        'background_color' => env('PWA_BACKGROUND_COLOR', '#18181B'),
        'theme_color' => env('PWA_THEME_COLOR', '#D8B4FE'),
        'lang' => env('PWA_LANG', 'en'),
        'dir' => env('PWA_DIR', 'ltr'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA Icons
    |--------------------------------------------------------------------------
    |
    | Define the icons for your PWA manifest. Each icon entry supports a
    | "disk" key (any configured Laravel filesystem disk) and a "path"
    | relative to that disk's root. The URL is resolved at generation time
    | via Storage::disk()->url(). Set "disk" to null to fall back to the
    | asset() helper with "path" used as-is.
    |
    */

    'icons' => [
        [
            'disk' => env('PWA_ICON_DISK', 'public'),
            'path' => env('PWA_ICON_MOBILE_PATH', 'images/icons/icon-192x192.png'),
            'sizes' => env('PWA_ICON_MOBILE_SIZES', '192x192'),
            'type' => env('PWA_ICON_MOBILE_TYPE', 'image/png'),
        ],
        [
            'disk' => env('PWA_ICON_DISK', 'public'),
            'path' => env('PWA_ICON_DESKTOP_PATH', 'images/icons/icon-512x512.png'),
            'sizes' => env('PWA_ICON_DESKTOP_SIZES', '512x512'),
            'type' => env('PWA_ICON_DESKTOP_TYPE', 'image/png'),
        ],
    ],

];

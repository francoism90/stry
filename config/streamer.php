<?php

declare(strict_types=1);

return [

    /**
     * Streamer (Shaka Streamer) configuration and settings.
     */
    'streamer' => [
        'python_binary' => env('STREAMER_PYTHON_BINARY', 'python3'),
        'streamer_binary' => env('STREAMER_BINARY', 'shaka-streamer'),
    ],

    /**
     * Whether to force using generic input paths for media files.
     */
    'force_generic_input' => env('STREAMER_FORCE_GENERIC_INPUT', true),

    /**
     * Timeout for the packaging process in seconds.
     */
    'timeout' => env('STREAMER_TIMEOUT', 60 * 60 * 4), // 4 hours

    /**
     * Log channel for streamer output. Set to false to disable logging.
     */
    'log_channel' => env('STREAMER_LOG_CHANNEL', null),

    /**
     * Root directory for temporary files used during streaming.
     */
    'temporary_files_root' => env('STREAMER_TEMPORARY_FILES_ROOT', storage_path('app/streamer/temp')),

    /**
     * Cache storage directory for small files (e.g., RAM disk like /dev/shm).
     * Used for encryption keys, manifests, and other small files that benefit from faster I/O.
     * NOT used for large video files - those use temporary_files_root to avoid consuming RAM.
     * Set to null to disable and use temporary_files_root for all operations.
     */
    'cache_files_root' => env('STREAMER_CACHE_FILES_ROOT', '/dev/shm'),

];

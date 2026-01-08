<?php

declare(strict_types=1);

return [

    /**
     * Path to the packager binary and other related settings.
     */
    'packager' => [
        'binaries' => env('PACKAGER_PATH', '/usr/local/bin/packager'),
    ],

    /**
     * Whether to force using generic input paths for media files.
     */
    'force_generic_input' => env('PACKAGER_FORCE_GENERIC_INPUT', true),

    /**
     * Timeout for the packaging process in seconds.
     */
    'timeout' => 60 * 60 * 4, // 4 hours

    /**
     * Log channel for packager output. Set to false to disable logging.
     */
    'log_channel' => env('PACKAGER_LOG_CHANNEL', false),

    /**
     * Root directory for temporary files used during packaging.
     */
    'temporary_files_root' => env('PACKAGER_TEMPORARY_FILES_ROOT', storage_path('app/packager/temp')),

    /**
     * Directory for encrypted temporary files (e.g., in-memory storage).
     */
    'temporary_files_encrypted' => env('PACKAGER_TEMPORARY_ENCRYPTED', '/dev/shm'),

];

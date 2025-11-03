<?php

declare(strict_types=1);

return [

    'ffmpeg' => [
        'binaries' => env('FFMPEG_PATH', 'ffmpeg'),

        'threads' => 0,
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_PATH', 'ffprobe'),
    ],

    'timeout' => 60 * 60 * 4, // 4 hours

    'log_channel' => env('FFMPEG_LOG_CHANNEL', env('APP_ENV') === 'production' ? false : ENV('LOG_CHANNEL', 'stack')),

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', '/dev/shm'),

];

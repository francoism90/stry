<?php

return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),

        'threads' => 0,
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
    ],

    'timeout' => 60 * 60 * 4, // 4 hours

    'log_channel' => env('LOG_CHANNEL', 'stack'),

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir())),
];

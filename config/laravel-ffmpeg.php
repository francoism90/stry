<?php

return [

    'ffmpeg' => [
        'binaries' => env('FFMPEG_PATH', '/usr/local/bin/ffmpeg'),

        'threads' => (int) env('FFMPEG_THREADS', 0),
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_PATH', '/usr/local/bin/ffprobe'),
    ],

    'timeout' => 60 * 60 * 4, // 4 hours

    'log_channel' => env('FFMPEG_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', storage_path('app/media/temp')),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', '/dev/shm'),

];

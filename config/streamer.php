<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Shaka Streamer Binary
    |--------------------------------------------------------------------------
    |
    | Path or command to execute the Shaka Streamer binary.
    |
    */

    'streamer' => [
        'streamer_binary' => env('STREAMER_BINARY', 'shaka-streamer'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Force Generic Input
    |--------------------------------------------------------------------------
    |
    | Whether to force using generic input paths for media files.
    |
    */

    'force_generic_input' => (bool) env('STREAMER_FORCE_GENERIC_INPUT', true),

    /*
    |--------------------------------------------------------------------------
    | Streaming Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout for the streaming process in seconds.
    | Default: 14400 seconds (4 hours)
    |
    */

    'timeout' => (int) env('STREAMER_TIMEOUT', 14400),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | Log channel for streamer output. Set to false to disable logging.
    |
    */

    'log_channel' => env('STREAMER_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    /*
    |--------------------------------------------------------------------------
    | Temporary Files Root
    |--------------------------------------------------------------------------
    |
    | Root directory for temporary files used during streaming.
    |
    */

    'temporary_files_root' => env('STREAMER_TEMPORARY_FILES_ROOT', storage_path('app/streamer/temp')),

    /*
    |--------------------------------------------------------------------------
    | Cache Files Root
    |--------------------------------------------------------------------------
    |
    | Cache storage directory for small files (e.g., RAM disk like /dev/shm).
    | Used for encryption keys, manifests, and other small files that benefit
    | from faster I/O. NOT used for large video files - those use
    | temporary_files_root to avoid consuming RAM. Set to null to disable
    | and use temporary_files_root for all operations.
    |
    */

    'cache_files_root' => env('STREAMER_CACHE_FILES_ROOT', '/dev/shm'),

    /*
    |--------------------------------------------------------------------------
    | Audio Codecs
    |--------------------------------------------------------------------------
    |
    | Default audio codecs to use for streaming. This can be overridden
    | on a per-stream basis when adding streams.
    |
    | Common options: 'aac', 'opus', 'mp3'
    | Specify as comma-separated string: STREAMER_AUDIO_CODECS="aac,opus"
    |
    */

    'audio_codecs' => env('STREAMER_AUDIO_CODECS', 'aac'),

    /*
    |--------------------------------------------------------------------------
    | Video Codecs
    |--------------------------------------------------------------------------
    |
    | Default video codecs to use for streaming. This can be overridden
    | on a per-stream basis when adding streams.
    |
    | Common options: 'h264', 'hw:h264', 'vp9', 'hw:vp9', 'av1'
    | Prefix with 'hw:' for hardware-accelerated encoding.
    | Specify as comma-separated string: STREAMER_VIDEO_CODECS="hw:h264,hw:vp9"
    |
    */

    'video_codecs' => env('STREAMER_VIDEO_CODECS', 'h264'),

    /*
    |--------------------------------------------------------------------------
    | Segment Duration
    |--------------------------------------------------------------------------
    |
    | Default duration of each segment in the stream, in seconds.
    | A typical value is between 4 and 10 seconds.
    |
    | Lower values: faster seeking, more HTTP requests
    | Higher values: fewer HTTP requests, slower seeking
    |
    */

    'segment_duration' => (int) env('STREAMER_SEGMENT_DURATION', 10),

    /*
    |--------------------------------------------------------------------------
    | Hardware Acceleration API
    |--------------------------------------------------------------------------
    |
    | Hardware acceleration API for video encoding.
    | Common options: 'vaapi', 'nvenc', 'videotoolbox', 'qsv'
    | Leave null to use software encoding.
    |
    */

    'hwaccel_api' => env('STREAMER_HWACCEL_API', null),

    /*
    |--------------------------------------------------------------------------
    | Extra Input Arguments
    |--------------------------------------------------------------------------
    |
    | Additional raw arguments passed directly to the packager's input.
    | Useful for advanced scenarios such as custom demuxer flags.
    | Leave null to pass no extra arguments.
    |
    */

    'extra_input_args' => env('STREAMER_EXTRA_INPUT_ARGS', null),

    /*
    |--------------------------------------------------------------------------
    | Shaka Streamer Options
    |--------------------------------------------------------------------------
    |
    | Additional configuration options for Shaka Streamer.
    | See: https://shaka-project.github.io/shaka-streamer/configuration_fields.html
    |
    | These options are merged with the pipeline configuration.
    |
    */

    'streamer_options' => [],

    /*
    |--------------------------------------------------------------------------
    | Concurrency Workers
    |--------------------------------------------------------------------------
    |
    | Maximum number of concurrent S3 uploads when copying streamed files
    | to an S3-backed disk. Ignored for local disks.
    |
    | Each in-flight upload holds an open file stream, so memory usage scales
    | with this value. Lower it if you run many parallel streaming jobs.
    |
    */

    'concurrency_workers' => (int) env('STREAMER_CONCURRENCY_WORKERS', 10),

];

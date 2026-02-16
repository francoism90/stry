<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logging Channel
    |--------------------------------------------------------------------------
    |
    | The log channel to use for ab-av1 encoding operations.
    | Default is the application's default log channel.
    |
    */
    'log_channel' => env('AB_AV1_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout (in seconds) for encoding operations.
    | Encodings can take a very long time, so adjust based on your needs.
    |
    */
    'timeout' => env('AB_AV1_TIMEOUT', 3600),

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | The default encoding preset to use.
    | Options: 0 (slowest, best quality) to 8 (fastest, lowest quality).
    |
    */
    'preset' => env('AB_AV1_PRESET', 8),

    /*
    |--------------------------------------------------------------------------
    | Default Encoder
    |--------------------------------------------------------------------------
    |
    | Default encoder to use for encoding.
    | Leave null to use ab-av1's default.
    | Options: av1_svtenc (CPU), av1_qsv (Intel QuickSync), av1_vaapi (AMD/Intel),
    |          libx264, libx265, etc.
    |
    */
    'encoder' => env('AB_AV1_ENCODER', null),

    /*
    |--------------------------------------------------------------------------
    | Encoder Arguments
    |--------------------------------------------------------------------------
    |
    | Additional arguments to pass directly to the encoder via --enc flag.
    | Example: 'look_ahead=1' for hardware encoder lookahead.
    |
    */
    'encoder_args' => env('AB_AV1_ENCODER_ARGS', null),

    /*
    |--------------------------------------------------------------------------
    | Pixel Format
    |--------------------------------------------------------------------------
    |
    | Pixel format for encoding.
    | Default: yuv420p10le (10-bit) for AV1, yuv420p (8-bit) for hardware.
    |
    */
    'pix_format' => env('AB_AV1_PIX_FORMAT', null),

    /*
    |--------------------------------------------------------------------------
    | Video Filter
    |--------------------------------------------------------------------------
    |
    | FFmpeg video filter to apply before encoding.
    | Example: 'scale=1920:1080' to resize video.
    |
    */
    'video_filter' => env('AB_AV1_VIDEO_FILTER', null),

    /*
    |--------------------------------------------------------------------------
    | Verbosity
    |--------------------------------------------------------------------------
    |
    | Logging verbosity level (0=normal, 1=-v, 2=-vv).
    | Higher values show per-sample VMAF and ffmpeg commands.
    |
    */
    'verbosity' => env('AB_AV1_VERBOSITY', 0),

    /*
    |--------------------------------------------------------------------------
    | Default Minimum VMAF
    |--------------------------------------------------------------------------
    |
    | Default VMAF quality target for auto-encode.
    | Range: 0-100, typical values 75-95
    |
    */
    'min_vmaf' => env('AB_AV1_MIN_VMAF', 95),

    /*
    |--------------------------------------------------------------------------
    | Maximum Encoded Percent
    |--------------------------------------------------------------------------
    |
    | Maximum allowed encode size as percentage of source.
    | Used to prevent oversized encodes.
    |
    */
    'max_encoded_percent' => env('AB_AV1_MAX_ENCODED_PERCENT', 200),

    /*
    |--------------------------------------------------------------------------
    | Sample Frames
    |--------------------------------------------------------------------------
    |
    | Number of frames to encode per sample (default: 240).
    | Lower values speed up the search phase.
    |
    */
    'vframes' => env('AB_AV1_VFRAMES', null),

    /*
    |--------------------------------------------------------------------------
    | Number of Samples
    |--------------------------------------------------------------------------
    |
    | Number of video samples to take for quality assessment (default: 6).
    | More samples increase accuracy for varied content.
    |
    */
    'samples' => env('AB_AV1_SAMPLES', null),

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Input Options (Hardware Acceleration)
    |--------------------------------------------------------------------------
    |
    | Additional FFmpeg input options for hardware acceleration.
    | These are passed to ab-av1 via --enc-input flag.
    |
    | Intel QuickSync (av1_qsv):
    |   ['hwaccel' => 'qsv', 'qsv_device' => '/dev/dri/renderD128']
    |
    | AMD VA-API (av1_vaapi):
    |   ['hwaccel' => 'vaapi', 'hwaccel_device' => '/dev/dri/renderD128',
    |    'hwaccel_output_format' => 'vaapi']
    |
    */
    'ffmpeg_input_options' => [
        // Intel QuickSync
        // 'hwaccel' => 'qsv',
        // 'qsv_device' => '/dev/dri/renderD128',

        // AMD VA-API
        // 'hwaccel' => 'vaapi',
        // 'hwaccel_device' => '/dev/dri/renderD128',
        // 'hwaccel_output_format' => 'vaapi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary Files Root
    |--------------------------------------------------------------------------
    |
    | Root directory for temporary files used during encoding.
    */
    'temporary_files_root' => env('AB_AV1_TEMPORARY_FILES_ROOT', storage_path('app/ab-av1/temp')),

    /*
    |--------------------------------------------------------------------------
    | Cache Files Root
    |--------------------------------------------------------------------------
    |
    | Cache storage directory for small files (e.g., RAM disk like /dev/shm).
    | Set to null to disable and use temporary_files_root for all operations.
    */
    'cache_files_root' => env('AB_AV1_CACHE_FILES_ROOT', '/dev/shm'),
];

<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | ab-av1 Binary
    |--------------------------------------------------------------------------
    |
    | Path or command to execute the ab-av1 binary.
    | Default: 'ab-av1' (assumes binary is in PATH)
    |
    | You can specify a custom path if ab-av1 is installed in a non-standard
    | location, or to use a specific version:
    |   - 'ab-av1' (default, uses PATH)
    |   - '/usr/local/bin/ab-av1'
    |   - '/home/user/.cargo/bin/ab-av1'
    |
    */

    'binary' => (string) env('AB_AV1_BINARY', 'ab-av1'),

    /*
    |--------------------------------------------------------------------------
    | Logging Channel
    |--------------------------------------------------------------------------
    |
    | The log channel to use for ab-av1 encoding operations.
    | Default is the application's default log channel.
    |
    */

    'log_channel' => env('AB_AV1_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout (in seconds) for encoding operations.
    | Encodings can take a very long time, so adjust based on your needs.
    |
    | Default: 14400 seconds (4 hours)
    |
    | Typical encoding times for 1080p content:
    | - 1 hour video @ preset 4: 2-6 hours (depending on CPU/GPU)
    | - 1 hour video @ preset 6: 1-3 hours
    | - 1 hour video @ preset 8: 30min-2 hours
    |
    | Hardware encoders (av1_qsv, av1_vaapi) are significantly faster.
    | Adjust timeout based on your hardware and typical video length.
    |
    */

    'timeout' => (int) env('AB_AV1_TIMEOUT', 14400),

    /*
    |--------------------------------------------------------------------------
    | Force Generic Input Paths
    |--------------------------------------------------------------------------
    |
    | Whether to force using a generic (symlinked/copied) input path for media
    | files. This prevents issues with special characters in filenames when
    | passing paths to ab-av1 on the command line.
    |
    */

    'force_generic_input' => (bool) env('AB_AV1_FORCE_GENERIC_INPUT', true),

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | The default encoding preset to use.
    | Range: 0 (slowest, best quality/compression) to 8 (fastest, lowest quality)
    |
    | Recommended presets:
    | - 0-2: Very slow, best compression (for archival)
    | - 3-4: Slow, excellent compression (good)
    | - 5-6: Medium speed, good compression (balanced)
    | - 7-8: Fast, lower compression (for quick encodes)
    |
    | Preset 4 offers excellent quality-to-speed ratio for 1080p content.
    | Lower presets take significantly longer but yield smaller files.
    |
    */

    'preset' => env('AB_AV1_PRESET', 6),

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
    | Example: 'av1_qsv_params=preset=slow:lookahead=1:lookahead_depth=60:extbrc=1'
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

    'verbosity' => (int) env('AB_AV1_VERBOSITY', 0),

    /*
    |--------------------------------------------------------------------------
    | Default Minimum VMAF
    |--------------------------------------------------------------------------
    |
    | Default VMAF quality target for auto-encode.
    | Range: 0-100 (higher = better quality, larger file size)
    |
    | Recommended values by use case:
    | - 95+: Near-transparent quality, large files
    | - 90-94: Excellent quality, good balance
    | - 85-89: High quality, smaller files
    | - 80-84: Good quality, noticeable compression
    | - 75-79: Acceptable for most content
    |
    | For 1080p content, 90 provides excellent quality with good compression.
    | Lower resolution content may need higher VMAF scores for the same
    | perceived quality. Higher resolutions (4K) can use lower VMAF values.
    |
    */

    'min_vmaf' => (int) env('AB_AV1_MIN_VMAF', 94),

    /*
    |--------------------------------------------------------------------------
    | Maximum Encoded Percent
    |--------------------------------------------------------------------------
    |
    | Maximum allowed encode size as percentage of source.
    | Used to prevent oversized encodes.
    | This is by default set to 300% to allow for cases where the source is already highly compressed.
    |
    */

    'max_encoded_percent' => (int) env('AB_AV1_MAX_ENCODED_PERCENT', 300),

    /*
    |--------------------------------------------------------------------------
    | Sample Frames
    |--------------------------------------------------------------------------
    |
    | Number of frames to encode per sample (default: 240, ~10 seconds at 24fps).
    | Lower values speed up the CRF search phase but may be less accurate.
    |
    | Recommended values:
    | - 240: Default, good balance (10 seconds @ 24fps, 8 seconds @ 30fps)
    | - 120-180: Faster search, still accurate for most content
    | - 60-100: Quick testing, less accurate
    |
    | For 1080p content, 240 frames provides reliable quality assessment.
    |
    */

    'vframes' => env('AB_AV1_VFRAMES', null),

    /*
    |--------------------------------------------------------------------------
    | Number of Samples
    |--------------------------------------------------------------------------
    |
    | Number of video samples to take for quality assessment (default: 6).
    | More samples increase accuracy for varied content but slow down encoding.
    |
    | Recommended values:
    | - 6: Default, good for most content
    | - 8-10: Better accuracy for content with varied scenes (recommended for 1080p)
    | - 4-5: Faster, suitable for uniform content
    | - 12+: Maximum accuracy, very slow
    |
    | For 1080p with mixed scenes (action, dialogue, etc.), 8-10 samples
    | provide better quality consistency across the entire video.
    |
    */

    'samples' => env('AB_AV1_SAMPLES', null),

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Input Options (Hardware Acceleration)
    |--------------------------------------------------------------------------
    |
    | Additional FFmpeg input options for hardware acceleration.
    | These are passed to ab-av1 via repeated --enc-input flags.
    |
    | Specify as a space-separated string in .env:
    |   AB_AV1_FFMPEG_INPUT_OPTIONS="hwaccel=vaapi hwaccel_output_format=vaapi"
    |
    | This will generate:
    |   --enc-input hwaccel=vaapi --enc-input hwaccel_output_format=vaapi
    |
    | Examples:
    | Intel QuickSync (av1_qsv):
    |   "hwaccel=qsv qsv_device=/dev/dri/renderD128"
    |
    | AMD VA-API (av1_vaapi):
    |   "hwaccel=vaapi hwaccel_device=/dev/dri/renderD128 hwaccel_output_format=vaapi"
    |
    */

    'ffmpeg_input_options' => env('AB_AV1_FFMPEG_INPUT_OPTIONS', null),

    /*
    |--------------------------------------------------------------------------
    | Temporary Files Root
    |--------------------------------------------------------------------------
    |
    | Root directory for temporary files used during encoding.
    */

    'temporary_files_root' => env('AB_AV1_TEMPORARY_FILES_ROOT', '/cache/temp/ab-av1'),

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

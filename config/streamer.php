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

    'temporary_files_root' => env('STREAMER_TEMPORARY_FILES_ROOT', '/cache/temp/streamer'),

    /*
    |--------------------------------------------------------------------------
    | Minimum Free Space
    |--------------------------------------------------------------------------
    |
    | Minimum free space (in bytes) required in temporary_files_root before
    | a new streaming job is allowed to start. Useful when this root is
    | backed by a size-limited mount (e.g. a tmpfs RAM disk), so a job
    | fails fast with a clear error instead of partway through streaming.
    |
    | This does NOT apply to cache_files_root - see cache_files_min_free
    | below, since that root is often sized very differently.
    |
    | Set to 0 to disable this check.
    |
    */

    'temporary_files_min_free' => (int) env('STREAMER_TEMPORARY_MIN_FREE', 0),

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
    | Cache Minimum Free Space
    |--------------------------------------------------------------------------
    |
    | Minimum free space (in bytes) required in cache_files_root before a
    | new cache directory is created there. Kept separate from
    | temporary_files_min_free because cache_files_root is often a much
    | smaller mount than temporary_files_root (e.g. a size-limited
    | /dev/shm), so the same floor rarely makes sense for both.
    |
    | Set to 0 to disable this check.
    |
    */

    'cache_files_min_free' => (int) env('STREAMER_CACHE_MIN_FREE', 0),

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

    'segment_duration' => (int) env('STREAMER_SEGMENT_DURATION', 6),

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

    'concurrency_workers' => (int) env('STREAMER_CONCURRENCY_WORKERS', 30),

    /*
    |--------------------------------------------------------------------------
    | Multipart Uploads
    |--------------------------------------------------------------------------
    |
    | Files at or above the threshold (in bytes) are uploaded to S3-backed
    | disks as a multipart upload, sending `multipart_concurrency` parts of
    | `multipart_part_size` bytes in parallel per file. This speeds up large
    | single-file outputs and is required for objects over 5 GB. Part size
    | must be at least 5 MB. Failed multipart uploads are aborted.
    |
    */

    'multipart_threshold' => (int) env('STREAMER_MULTIPART_THRESHOLD', 64 * 1024 * 1024),

    'multipart_part_size' => (int) env('STREAMER_MULTIPART_PART_SIZE', 16 * 1024 * 1024),

    'multipart_concurrency' => (int) env('STREAMER_MULTIPART_CONCURRENCY', 5),

];

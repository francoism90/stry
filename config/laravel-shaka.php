<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Packager Binaries
    |--------------------------------------------------------------------------
    |
    | Path to the Shaka Packager binary executable.
    |
    */

    'packager' => [
        'binaries' => (string) env('PACKAGER_PATH', 'packager'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Segment Duration
    |--------------------------------------------------------------------------
    |
    | Default duration of each segment in seconds.
    | A typical value is between 4 and 10 seconds.
    |
    | Lower values: faster seeking, more HTTP requests
    | Higher values: fewer HTTP requests, slower seeking
    |
    */

    'segment_duration' => (int) env('PACKAGER_SEGMENT_DURATION', 6),

    /*
    |--------------------------------------------------------------------------
    | Packager Options
    |--------------------------------------------------------------------------
    |
    | Configuration options for Shaka Packager.
    | For more information, visit: https://shaka-project.github.io/shaka-packager/html/options.html
    |
    */

    'packager_options' => env('PACKAGER_OPTIONS', null),

    /*
    |--------------------------------------------------------------------------
    | Force Generic Input Paths
    |--------------------------------------------------------------------------
    |
    | Whether to force using generic input paths for media files.
    | This can help normalize path handling across different systems.
    |
    */

    'force_generic_input' => (bool) env('PACKAGER_FORCE_GENERIC_INPUT', true),

    /*
    |--------------------------------------------------------------------------
    | Packaging Process Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout for the packaging process in seconds.
    | Default: 14400 seconds (4 hours)
    |
    */

    'timeout' => (int) env('PACKAGER_TIMEOUT', 14400),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel for packager output. Set to null to use the default channel,
    | or false to disable logging entirely.
    |
    */

    'log_channel' => env('PACKAGER_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    /*
    |--------------------------------------------------------------------------
    | Temporary Files Root
    |--------------------------------------------------------------------------
    |
    | Root directory for temporary files used during the packaging process.
    | These are typically large video chunks and intermediate files.
    |
    */

    'temporary_files_root' => (string) env('PACKAGER_TEMPORARY_FILES_ROOT', '/cache/temp/packager'),

    /*
    |--------------------------------------------------------------------------
    | Minimum Free Space
    |--------------------------------------------------------------------------
    |
    | Minimum free space (in bytes) required in temporary_files_root before
    | a new packaging job is allowed to start. Useful when this root is
    | backed by a size-limited mount (e.g. a tmpfs RAM disk), so a job
    | fails fast with a clear error instead of partway through packaging.
    |
    | This does NOT apply to cache_files_root - see cache_files_min_free
    | below, since that root is often sized very differently.
    |
    | Set to 0 to disable this check.
    |
    */

    'temporary_files_min_free' => (int) env('PACKAGER_TEMPORARY_MIN_FREE', 0),

    /*
    |--------------------------------------------------------------------------
    | Size Safety Multiplier
    |--------------------------------------------------------------------------
    |
    | Before packaging starts, the combined size of the job's source input
    | files is multiplied by this value and checked against free space in
    | temporary_files_root (on top of temporary_files_min_free). Packager
    | repackages/segments already-encoded input rather than re-encoding it,
    | so output size tracks input size closely; this multiplier covers
    | container/segmentation overhead.
    |
    | Tune it from real usage: compare `du -sh` on a finished job's temp
    | directory against the size of its source input files.
    |
    */

    'temporary_files_size_multiplier' => (float) env('PACKAGER_TEMPORARY_SIZE_MULTIPLIER', 1.5),

    /*
    |--------------------------------------------------------------------------
    | Cache Files Root
    |--------------------------------------------------------------------------
    |
    | Cache storage directory for small files (e.g., RAM disk like /dev/shm).
    |
    | Used for:
    |   - Encryption keys
    |   - Manifests
    |   - Other small files that benefit from faster I/O
    |
    | NOT used for large video files, which use temporary_files_root
    | to avoid consuming excessive RAM.
    |
    | Set to null to disable and use temporary_files_root for all operations.
    |
    */

    'cache_files_root' => (string) env('PACKAGER_CACHE_FILES_ROOT', '/dev/shm'),

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

    'cache_files_min_free' => (int) env('PACKAGER_CACHE_MIN_FREE', 0),

    /*
    |--------------------------------------------------------------------------
    | Concurrency Workers
    |--------------------------------------------------------------------------
    |
    | Maximum number of concurrent S3 uploads when copying packaged files
    | to an S3-backed disk. Ignored for local disks.
    |
    */

    'concurrency_workers' => (int) env('PACKAGER_CONCURRENCY_WORKERS', 30),

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

    'multipart_threshold' => (int) env('PACKAGER_MULTIPART_THRESHOLD', 64 * 1024 * 1024),

    'multipart_part_size' => (int) env('PACKAGER_MULTIPART_PART_SIZE', 16 * 1024 * 1024),

    'multipart_concurrency' => (int) env('PACKAGER_MULTIPART_CONCURRENCY', 5),

];

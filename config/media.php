<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The disk media is opened from when Media::open() is called without
    | fromDisk(). Set to null to use the application's default disk.
    |
    */

    'disk' => env('MEDIA_DISK'),

    /*
    |--------------------------------------------------------------------------
    | Executables
    |--------------------------------------------------------------------------
    |
    | The path or command name of each executable. An absolute path is used
    | as-is. A command name is looked up in the PATH and in the application's
    | base path, so a static binary placed in the project root is found too.
    |
    | Executables are resolved when they are first used, so only the tools
    | you actually call need to be installed.
    |
    */

    'executables' => [
        'ffmpeg' => env('MEDIA_FFMPEG_PATH', env('FFMPEG_PATH', '/usr/local/bin/ffmpeg')),
        'ffprobe' => env('MEDIA_FFPROBE_PATH', env('FFPROBE_PATH', '/usr/local/bin/ffprobe')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Packager
    |--------------------------------------------------------------------------
    |
    | The driver that packages encoded media into DASH and HLS. "native" needs
    | only ffmpeg. Register others with PackagerManager::extend().
    |
    */

    'packager' => [
        'default' => env('MEDIA_PACKAGER', 'native'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ladder
    |--------------------------------------------------------------------------
    |
    | Where $opener->ladder() decodes, scales and encodes: "none" (the CPU),
    | "vaapi" (Intel and AMD on Linux, through the render device), "nvenc"
    | (NVIDIA) or "qsv" (Intel Quick Sync). A Ladder can override it. VAAPI and
    | Quick Sync open vaapi_device: renderD128 is the first GPU, renderD129 the
    | second. When a GPU can't be opened, the CPU encodes instead.
    |
    */

    'ladder' => [
        'hardware' => env('MEDIA_LADDER_HARDWARE', 'none'),
        'vaapi_device' => env('MEDIA_LADDER_VAAPI_DEVICE', '/dev/dri/renderD128'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    |
    | Streaming straight from the stored files, packaging segments when they
    | are requested. Keyframe indexes are kept in the cache store (null for the
    | default store) for index_lifetime seconds, per version of a file.
    |
    | Packaged segments are cached on cache_disk under cache_path, which can be
    | any disk: local storage, a mounted /tmp or RAM disk, or S3. Segments on
    | disks with temporary URLs are served by redirecting to one that is valid
    | for url_lifetime seconds. lock_timeout is how long concurrent requests
    | for the same segment wait while it's packaged. Schedule "media:prune" to
    | delete old segments; they are packaged again when requested.
    |
    | After a segment is requested, the next look_ahead segments are packaged
    | ahead of their requests, and a playlist or manifest request packages the
    | first ones: "queue" dispatches a PackageSegments job on the look-ahead
    | connection and queue, "defer" packages them after the response, and null
    | turns it off. Streams on a sync queue, or on disks without a configured
    | name, are packaged after the response.
    |
    | Renditions encoded on request use hardware ("none", "vaapi", "nvenc" or
    | "qsv"), or the ladder's hardware when it's null.
    |
    */

    'delivery' => [
        'segment_duration' => (float) env('MEDIA_DELIVERY_SEGMENT_DURATION', 6),
        'cache_disk' => env('MEDIA_DELIVERY_CACHE_DISK', 'cache'),
        'cache_path' => env('MEDIA_DELIVERY_CACHE_PATH', 'media-segments'),
        'url_lifetime' => (int) env('MEDIA_DELIVERY_URL_LIFETIME', 14400),
        'lock_timeout' => (int) env('MEDIA_DELIVERY_LOCK_TIMEOUT', 120),
        'cache_store' => env('MEDIA_DELIVERY_CACHE_STORE'),
        'index_lifetime' => (int) env('MEDIA_DELIVERY_INDEX_LIFETIME', 604800),
        'look_ahead' => (int) env('MEDIA_DELIVERY_LOOK_AHEAD', 3),
        'look_ahead_via' => env('MEDIA_DELIVERY_LOOK_AHEAD_VIA', 'queue'),
        'look_ahead_connection' => env('MEDIA_DELIVERY_LOOK_AHEAD_CONNECTION', 'redis-media'),
        'look_ahead_queue' => env('MEDIA_DELIVERY_LOOK_AHEAD_QUEUE', 'media'),
        'hardware' => env('MEDIA_DELIVERY_HARDWARE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Playback
    |--------------------------------------------------------------------------
    |
    | The codecs browsers play through a direct stream, by their ffprobe name.
    | H.264 also has to be 8-bit 4:2:0, and the others 4:2:0 at 8 or 10 bits.
    | Remove "hevc" for Firefox, which only decodes it experimentally. Streams
    | that don't play are re-encoded by $opener->makePlayable(): video with
    | video_codec ("libx264", "libx265" or "libsvtav1") at the crf and preset
    | (the codec's defaults when null), audio as AAC at audio_bitrate kbit/s.
    | Set hardware to "vaapi", "nvenc" or "qsv" to encode that video on the
    | GPU (decoding stays on the CPU); null, "none" or a GPU that can't be
    | opened encodes on the CPU.
    |
    */

    'playback' => [
        'video_codecs' => explode(',', (string) env('MEDIA_PLAYBACK_VIDEO_CODECS', 'hevc,h264,av1,vp9')),
        'audio_codecs' => explode(',', (string) env('MEDIA_PLAYBACK_AUDIO_CODECS', 'aac,mp3,opus,flac')),
        'video_codec' => env('MEDIA_PLAYBACK_VIDEO_CODEC', 'libx264'),
        'crf' => env('MEDIA_PLAYBACK_CRF'),
        'preset' => env('MEDIA_PLAYBACK_PRESET'),
        'audio_bitrate' => (int) env('MEDIA_PLAYBACK_AUDIO_BITRATE', 192),
        'hardware' => env('MEDIA_PLAYBACK_HARDWARE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds a single process may run.
    |
    */

    'timeout' => (int) env('MEDIA_TIMEOUT', 14400),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel used for process logging. Set to false to disable
    | logging, or null to use the application's default channel.
    |
    */

    'log_channel' => env('MEDIA_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Log Level
    |--------------------------------------------------------------------------
    |
    | What ffmpeg writes to its error output. Successful runs that still wrote
    | something are logged as warnings on the log channel. Set this to
    | "warning" to see why output from damaged files looks wrong.
    |
    */

    'ffmpeg_log_level' => env('MEDIA_FFMPEG_LOG_LEVEL', 'error'),

    /*
    |--------------------------------------------------------------------------
    | Remote Inputs
    |--------------------------------------------------------------------------
    |
    | When enabled, media on remote disks that provide temporary URLs (such as
    | S3) is read by ffmpeg and ffprobe through a short-lived signed URL
    | instead of being downloaded first. ffprobe then only fetches the parts
    | of the file it needs.
    |
    | FFmpeg 9 verifies TLS certificates by default. For storage behind a
    | private CA, point "ca_file" at its certificate; set "verify_tls" to
    | false to accept any certificate, e.g. a self-signed one in development.
    |
    */

    'remote_inputs' => [
        'enabled' => (bool) env('MEDIA_REMOTE_INPUTS', true),
        'url_lifetime' => (int) env('MEDIA_REMOTE_INPUTS_URL_LIFETIME', 3600),
        'verify_tls' => (bool) env('MEDIA_REMOTE_INPUTS_VERIFY_TLS', true),
        'ca_file' => env('MEDIA_REMOTE_INPUTS_CA_FILE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary Files
    |--------------------------------------------------------------------------
    |
    | Where downloaded inputs and process outputs are written before they
    | are copied to their target disk.
    |
    | The cache root is used for small files such as encryption keys, and can
    | point to a RAM disk like /dev/shm. Set it to null to use the regular root.
    |
    | The minimum free space settings (in bytes) make a job fail early when a
    | size-limited mount is full. Set them to 0 to disable the check.
    |
    | Temporary directories are deleted after every queue job when
    | cleanup_after_jobs is enabled. Schedule "media:clean" to remove what's
    | left behind after a crash.
    |
    */

    'temporary_files' => [
        'root' => env('MEDIA_TEMPORARY_FILES_ROOT', '/cache/temp/media'),
        'min_free' => (int) env('MEDIA_TEMPORARY_FILES_MIN_FREE', 0),
        'size_multiplier' => (float) env('MEDIA_TEMPORARY_FILES_SIZE_MULTIPLIER', 1.5),
        'cache_root' => env('MEDIA_CACHE_FILES_ROOT'),
        'cache_min_free' => (int) env('MEDIA_CACHE_FILES_MIN_FREE', 0),
        'cleanup_after_jobs' => (bool) env('MEDIA_CLEANUP_AFTER_JOBS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | How results are copied to S3 disks. Up to `concurrency` files upload at
    | the same time. Files of at least `multipart_threshold` bytes are sent as
    | a multipart upload of `multipart_part_size` byte parts (at least 5 MB),
    | `multipart_concurrency` parts at a time. Multipart uploads are required
    | for objects over 5 GB, and failed ones are aborted.
    |
    | When a file fails to upload, rollback_on_failure deletes the files of
    | the same export that did reach the disk, so no half result is left.
    |
    | Other disks receive files one at a time.
    |
    */

    'uploads' => [
        'concurrency' => (int) env('MEDIA_UPLOADS_CONCURRENCY', 10),
        'multipart_threshold' => (int) env('MEDIA_UPLOADS_MULTIPART_THRESHOLD', 64 * 1024 * 1024),
        'multipart_part_size' => (int) env('MEDIA_UPLOADS_MULTIPART_PART_SIZE', 16 * 1024 * 1024),
        'multipart_concurrency' => (int) env('MEDIA_UPLOADS_MULTIPART_CONCURRENCY', 5),
        'rollback_on_failure' => (bool) env('MEDIA_UPLOADS_ROLLBACK_ON_FAILURE', true),
    ],

];

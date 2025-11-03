<?php

declare(strict_types=1);

return [

    /**
     * This setting is used to define the disk where the playlist will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'disk_name' => env('PLAYLIST_DISK', 'segments'),

    /**
     * This setting is used to define the free disk space requirements for playlists.
     * The value is in bytes, and it will be used to limit the size of the playlist storage.
     */
    'disk_size' => (int) env('PLAYLIST_DISK_SIZE', 1073741824 * 4), // 4 GB

    /**
     * This setting is used to define the expiration time for the playlist.
     * The value is in seconds, and it will be used to determine how long the playlist will be valid.
     * After this time, the playlist will be considered expired and can be prunable.
     */
    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 14), // 14 days

    /**
     * This setting is used to define the time after which the playlist will be considered stale.
     * The value is in seconds, and it will be used to determine how long the playlist will be valid.
     * After this time, the playlist will be considered stale and can be prunable.
     */
    'stale_after' => (int) env('PLAYLIST_STALE_AFTER', 60 * 60 * 24 * 7), // 7 days

    /**
     * This setting is used to define the middleware that will be applied to the playlist routes.
     * You can add or remove middleware as needed.
     */
    'middleware' => [
        'signed',
        'auth:sanctum',
        'verified',
        'subscribed',
        'cache:private;max_age=259200;immutable',
    ],

    /**
     * This setting is used to configure the HLS formats that will be used for the playlist.
     * You can define multiple formats with different bit rates.
     * At least one format must be defined.
     */
    'hls_formats' => [
        ['name' => 'default', 'kilo_bitrate' => 6000],
        // ['name' => 'low', 'kilo_bitrate' => 500],
        // ['name' => 'mid', 'kilo_bitrate' => 3000],
        // ['name' => 'high', 'kilo_bitrate' => 6000],
        // ['name' => 'ultra', 'kilo_bitrate' => 9000],
    ],

    /**
     * This setting is used to configure the segment length for the playlist.
     * The segment length is the duration of each segment in seconds.
     * A shorter segment length will result in more segments, which can improve seeking performance,
     * but will also increase the file size and requests.
     */
    'segment_length' => (int) env('PLAYLIST_SEGMENT_LENGTH', 6),

    /**
     * This setting is used to configure the frame interval for the playlist.
     * The frame interval is the number of frames between each keyframe in the video.
     * A lower frame interval will result in more keyframes, which can improve seeking performance,
     * but will also increase the file size.
     */
    'frame_interval' => (int) env('PLAYLIST_FRAME_INTERVAL', 60),

    /**
     * This setting is used to configure the video formats that will be used for transcoding.
     * The order of the formats will determine the priority of transcoding.
     * The first format in the list will be used as the fallback format for transcoding.
     * At least one format must be defined.
     */
    'video_formats' => [
        \Support\FFMpeg\Format\Video\X264::class,
        \Support\FFMpeg\Format\Video\X265::class,
        \Support\FFMpeg\Format\Video\WebM::class,
    ],

    /**
     * This setting is used to configure the additional parameters that will be passed to the FFMpeg exporter.
     * These parameters will be used to configure the transcoding process.
     */
    'additional_parameters' => [
        // '-force_key_frames:v', 'expr:gte(t,n_forced*2.000)',
    ],

    /**
     * When this setting is true, the playlist will copy the video codec from the original video file.
     * This is useful for cases where the original video file is already in a compatible video codec.
     * It can also improve performance by avoiding the need to transcode the video.
     */
    'copy_video_codec' => (bool) env('PLAYLIST_COPY_VIDEO_CODEC', true),

    /**
     * When this setting is true, the playlist will copy the audio codec from the original audio file.
     * This is useful for cases where the original video file is already in a compatible audio codec.
     * It can also improve performance by avoiding the need to transcode the audio.
     */
    'copy_audio_codec' => (bool) env('PLAYLIST_COPY_AUDIO_CODEC', true),

    /**
     * This setting is used to enable or disable the rotation keys (encryption) for playlists.
     * When enabled, the playlist segments will be encrypted using AES-128 encryption.
     * This can help to protect the content from unauthorized access.
     */
    'rotation_keys' => (bool) env('PLAYLIST_ROTATION_KEYS', true),

    /**
     * This setting is used to define the number of sections for the rotation keys.
     * Each section will have its own key, which will be used to encrypt the segments of the playlist.
     * A lower number of sections will result in more keys, which can decrease performance.
     */
    'rotation_keys_sections' => (int) env('PLAYLIST_ROTATION_KEYS_SECTIONS', 5),

    /**
     * This setting is used to define the disk where the rotation keys will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'rotation_keys_disk' => env('PLAYLIST_ROTATION_KEYS_DISK', 'secrets'),

    /**
     * This setting is used to define the required free disk space for the rotation keys.
     * The value is in bytes, and it will be used to limit the size of the rotation keys storage.
     */
    'rotation_keys_disk_size' => (int) env('PLAYLIST_ROTATION_DISK_SIZE', 1073741824 * 1), // 1 GB

];

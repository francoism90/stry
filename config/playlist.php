<?php

declare(strict_types=1);

return [

    /**
     * This setting is used to define the disk where the playlist will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'disk_name' => env('PLAYLIST_DISK', 'export'),

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
        // 'cache:private;max_age=259200;immutable',
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
    'frame_interval' => (int) env('PLAYLIST_FRAME_INTERVAL', 180),

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

];

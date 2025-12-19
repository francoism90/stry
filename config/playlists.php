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
     * This setting is used to define the disk where the rotation keys will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'secret_disk' => env('PLAYLIST_SECRET_DISK', 'secrets'),

    /**
     * This setting is used to configure the segment length for the playlist.
     * The segment length is the duration of each segment in seconds.
     * A shorter segment length will result in more segments, which can improve seeking performance,
     * but will also increase the file size and requests.
     */
    'segment_length' => (int) env('PLAYLIST_SEGMENT_LENGTH', 6),

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

];

<?php

return [

    /**
     * This setting is used to define the disk where the playlists will be stored.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'disk_name' => env('PLAYLIST_DISK', 'segments'),

    /**
     * This setting is used to define the duration of each segment in the playlist, in seconds.
     * A typical value is between 4 and 10 seconds.
     */
    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 6),

    'key_duration' => (string) env('PLAYLIST_ENCRYPTION', 'raw_key_encryption'),

    /**
     * This setting is used to define the time after which the playlist will expire.
     * The value is in seconds, and it will be used to set the expiration time for the playlist.
     * Set to 0 for no expiration.
     */
    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 5), // 5 days

];

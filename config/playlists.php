<?php

declare(strict_types=1);

return [

    /**
     * This setting is used to define the disk where the playlists will be stored.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'disk_name' => env('PLAYLIST_DISK', 'segments'),

    /**
     * This setting is used to define the disk where the playlist encryption keys will be stored.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'secret_disk' => env('PLAYLIST_SECRET_DISK', 'secrets'),

    /**
     * This setting is used to define the encryption method for the playlist.
     * Set to 'raw_key_encryption' to enable AES-128 encryption with key URI for browser compatibility.
     * Set to any other value to disable encryption.
     */
    'encryption' => (string) env('PLAYLIST_ENCRYPTION', 'raw_key_encryption'),

    /**
     * This setting is used to define the time after which the playlist will expire.
     * The value is in seconds, and it will be used to set the expiration time for the playlist.
     */
    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 7), // 7 days

];

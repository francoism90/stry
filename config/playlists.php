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
    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 10),

    /**
     * This setting is used to define the encryption method for the playlist.
     *
     * Set to 'raw_key_encryption' to enable AES-128-CBC encryption (browser-compatible)
     * Set to 'none' or any other value to disable encryption.
     */
    'encryption' => (string) env('PLAYLIST_ENCRYPTION', 'raw_key_encryption'),

    /**
     * Protection scheme for encryption.
     *
     * - 'cenc' (AES-CTR) for Widevine/PlayReady - best for key rotation
     * - 'cbcs' (AES-CBC) for FairPlay/Safari
     * - 'cbc1' legacy HLS, limited browser support
     * - null (SAMPLE-AES) widest compatibility but NO key rotation support
     */
    'protection_scheme' => (string) env('PLAYLIST_PROTECTION_SCHEME', 'cbcs'),

    /**
     * Enable encryption key rotation. When enabled, new keys are generated at specified intervals.
     */
    'key_rotation' => (bool) env('PLAYLIST_KEY_ROTATION', false),

    /**
     * Duration in seconds before rotating to a new encryption key.
     * Common values: 60 (1 min), 300 (5 min), 600 (10 min), 1800 (30 min).
     */
    'key_rotation_duration' => (int) env('PLAYLIST_KEY_ROTATION_DURATION', 300),

    /**
     * This setting is used to define the time after which the playlist will expire.
     * The value is in seconds, and it will be used to set the expiration time for the playlist.
     * Set to 0 for no expiration.
     */
    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 5), // 5 days

    /**
     * Shaka Packager options.
     *
     * @see https://shaka-project.github.io/shaka-packager/html/options.html
     *
     * - transport_stream_timestamp_offset_ms: Timestamp offset for transport streams (improves compatibility)
     * - num_subsegments_per_sidx: Number of subsegments per SIDX box (0 = disable, reduces overhead)
     * - fragment_sap_aligned: Align fragments to stream access points (improves seeking performance)
     */
    'packager_options' => [
        'transport_stream_timestamp_offset_ms' => 1000,
        'num_subsegments_per_sidx' => 0,
        'fragment_sap_aligned' => true,
    ],

];

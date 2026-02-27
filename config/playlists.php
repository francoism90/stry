<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Playlist Type
    |--------------------------------------------------------------------------
    |
    | This value determines the mode of playlist generation. Can be either
    | 'packager' or 'streamer'. Packager repurposes existing media files
    | without re-encoding (fastest). Streamer generates playlists on-the-fly
    | from source media (slower, but with more options).
    |
    */

    'type' => (string) env('PLAYLIST_TYPE', 'packager'),

    /*
    |--------------------------------------------------------------------------
    | Playlist Disk
    |--------------------------------------------------------------------------
    |
    | This value determines the disk where playlists will be stored.
    | The disk should be configured in the filesystems configuration.
    |
    */

    'disk_name' => (string) env('PLAYLIST_DISK', 'segments'),

    /*
    |--------------------------------------------------------------------------
    | Segment Duration
    |--------------------------------------------------------------------------
    | This value determines the target duration of each media segment in seconds.
    | Common values are 6, 10, or 12 seconds. Shorter durations can reduce latency but may
    | increase overhead. Longer durations can improve efficiency but may increase latency.
    |
    */

    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 10),

    /*
    |--------------------------------------------------------------------------
    | Fragment Duration
    |--------------------------------------------------------------------------
    | This value determines the target duration of each fragment in seconds.
    | Fragments are used for fMP4 segments and can help with faster start times.
    | Common values are 2 or 4 seconds. Setting to 0 will use the default value.
    |
    */

    'fragment_duration' => (int) env('PLAYLIST_FRAGMENT_DURATION', 2),

    /*
    |--------------------------------------------------------------------------
    | Encryption Method
    |--------------------------------------------------------------------------
    |
    | This value determines the encryption method for the playlist.
    | Options: 'raw_key_encryption' (AES-128 SAMPLE-AES, requires TS),
    | 'clearkey' (W3C Clear Key EME, works with fMP4, browser-native),
    | or null for no encryption.
    |
    */

    'encryption' => (string) env('PLAYLIST_ENCRYPTION', 'raw_key_encryption'),

    /*
    |--------------------------------------------------------------------------
    | Protection Scheme
    |--------------------------------------------------------------------------
    |
    | Protection scheme for encryption:
    | - 'cenc' (AES-CTR) for Widevine/PlayReady/Clear Key (best for HLS
    |   with fMP4 and key rotation)
    | - 'cbcs' (AES-CBC) for FairPlay/Safari (use with DASH, not HLS)
    | - 'cbc1' legacy HLS, limited browser support
    | - null (SAMPLE-AES) widest compatibility with TS segments, no
    |   key rotation support
    |
    */

    'protection_scheme' => (string) env('PLAYLIST_PROTECTION_SCHEME', 'cenc'),

    /*
    |--------------------------------------------------------------------------
    | Key Rotation
    |--------------------------------------------------------------------------
    |
    | Enable encryption key rotation. When enabled, new keys are
    | generated at specified intervals.
    |
    */

    'key_rotation' => (bool) env('PLAYLIST_KEY_ROTATION', false),

    /*
    |--------------------------------------------------------------------------
    | Key Rotation Duration
    |--------------------------------------------------------------------------
    |
    | Duration in seconds before rotating to a new encryption key.
    | Common values: 60 (1 min), 300 (5 min), 600 (10 min), 1800 (30 min).
    |
    */

    'key_rotation_duration' => (int) env('PLAYLIST_KEY_ROTATION_DURATION', 300),

    /*
    |--------------------------------------------------------------------------
    | Playlist Expiration
    |--------------------------------------------------------------------------
    |
    | This value determines the time after which the playlist will expire,
    | in seconds. Set to 0 for no expiration. Default is 14 days.
    |
    */

    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 14), // 14 days

];

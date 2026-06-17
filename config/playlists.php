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
    | Playlist Language
    |--------------------------------------------------------------------------
    |
    | This value determines the default language for playlists.
    |
    */

    'language' => (string) env('PLAYLIST_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Playlist Text Language
    |--------------------------------------------------------------------------
    |
    | This value determines the default text language for playlists.
    |
    */

    'text_language' => (string) env('PLAYLIST_TEXT_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Buffer Time
    |--------------------------------------------------------------------------
    |
    | This value determines the minimum buffer time for the playlist in seconds.
    | A common value is 2 seconds. Setting to 0 will use the default value.
    |
    */

    'buffer_time' => (int) env('PLAYLIST_BUFFER_TIME', 2),

    /*
    |--------------------------------------------------------------------------
    | Segment Duration
    |--------------------------------------------------------------------------
    |
    | This value determines the target duration of each media segment in seconds.
    | Common values are 6, 10, or 12 seconds. Shorter durations can reduce latency but may
    | increase overhead. Longer durations can improve efficiency but may increase latency.
    |
    */

    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 6),

    /*
    |--------------------------------------------------------------------------
    | Fragment Duration
    |--------------------------------------------------------------------------
    |
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
    | This value determines the encryption method for the streaming segments.
    | Options:
    | - 'raw_key_encryption': Standard AES-128 Envelope Encryption. The player
    |   decrypts the segments in memory before decoding. Native hardware paths
    |   remain open (Allows HEVC on Linux/Firefox). Requires TS or fMP4 segments.
    | - 'clearkey': W3C Clear Key EME. Native browser-level decryption using
    |   Common Encryption. Does NOT require external DRM license servers. Note:
    |   Firefox blocks ClearKey when used with HEVC profiles, but fully supports AV1/H.264.
    | - null: No encryption. Media streams completely in the clear.
    |
    */

    'encryption' => (string) env('PLAYLIST_ENCRYPTION', ''),

    /*
    |--------------------------------------------------------------------------
    | Protection Scheme
    |--------------------------------------------------------------------------
    |
    | Protection scheme used alongside native browser EME (clearkey):
    | - 'cenc' : AES-CTR mode. The cross-browser standard for DASH pipelines
    |            and modern fragmented MP4 (fMP4) architectures.
    | - 'cbcs' : AES-CBC mode. Primarily utilized for Apple FairPlay and modern
    |            unified HLS/CMAF cross-platform deployments.
    | - null   : No protection scheme mapping. Required when utilizing traditional
    |            'raw_key_encryption' envelope encryption frameworks.
    |
    */

    'protection_scheme' => (string) env('PLAYLIST_PROTECTION_SCHEME', ''),

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

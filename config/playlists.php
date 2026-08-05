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
    | Manifest Cache Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime (in seconds) for caching generated DASH manifests.
    | Keep this shorter than media URL lifetime so cached manifests don't carry
    | stale signed segment URLs.
    |
    */

    'manifest_cache_lifetime' => (int) env('PLAYLIST_MANIFEST_CACHE_LIFETIME', 300),

    /*
    |--------------------------------------------------------------------------
    | Manifest URL Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime (in seconds) for the signed manifest URL. This protects the
    | playlist route itself (api.play.manifest).
    |
    */

    'manifest_url_lifetime' => (int) env('PLAYLIST_MANIFEST_URL_LIFETIME', 7200),

    /*
    |--------------------------------------------------------------------------
    | Manifest Refresh Before
    |--------------------------------------------------------------------------
    |
    | While a video is actively being watched, the video session heartbeat
    | re-signs and broadcasts a fresh manifest URL this many seconds before
    | the current one expires, so long-running playback never hits an
    | expired signed URL.
    |
    */

    'manifest_refresh_before' => (int) env('PLAYLIST_MANIFEST_REFRESH_BEFORE', 300),

    /*
    |--------------------------------------------------------------------------
    | Media URL Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime (in seconds) for signed segment / init URLs embedded into the
    | manifest. Keep this longer than manifest cache lifetime to avoid mid-play
    | expiry during long sessions.
    |
    */

    'media_url_lifetime' => (int) env('PLAYLIST_MEDIA_URL_LIFETIME', 7200),

    /*
    |--------------------------------------------------------------------------
    | Key URL Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime (in seconds) for signed encryption key URLs. If null, media URL
    | lifetime will be used.
    |
    */

    'key_url_lifetime' => (int) env('PLAYLIST_KEY_URL_LIFETIME', 7200),

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

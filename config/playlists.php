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
    | Audio Codecs
    |--------------------------------------------------------------------------
    |
    | Default audio codecs to use for the playlist. This can be overridden
    | on a per-stream basis when adding streams to the playlist.
    | Note: Only applicable when using Shaka Streamer.
    |
    */

    'audio_codecs' => (array) env('PLAYLIST_AUDIO_CODECS', ['aac']),

    /*
    |--------------------------------------------------------------------------
    | Video Codecs
    |--------------------------------------------------------------------------
    |
    | Default video codecs to use for the playlist. This can be overridden
    | on a per-stream basis when adding streams to the playlist.
    | Note: Only applicable when using Shaka Streamer.
    |
    */

    'video_codecs' => (array) env('PLAYLIST_VIDEO_CODECS', ['hw:h264']),

    /*
    |--------------------------------------------------------------------------
    | Resolutions
    |--------------------------------------------------------------------------
    |
    | This value determines the resolutions generated for the playlist.
    | An array of strings, each representing a resolution (e.g., '1080p').
    | Note: Only applicable when using Shaka Streamer.
    |
    */

    'resolutions' => (array) env('PLAYLIST_RESOLUTIONS', []),

    /*
    |--------------------------------------------------------------------------
    | Segment Duration
    |--------------------------------------------------------------------------
    |
    | This value determines the duration of each segment in the playlist,
    | in seconds. A typical value is between 4 and 10 seconds.
    |
    */

    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 10),

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

    /*
    |--------------------------------------------------------------------------
    | Shaka Packager Options
    |--------------------------------------------------------------------------
    |
    | Configuration options for Shaka Packager.
    | See: https://shaka-project.github.io/shaka-packager/html/options.html
    |
    | - num_subsegments_per_sidx: Number of subsegments per SIDX box
    |   (0 = disable, reduces overhead)
    | - fragment_sap_aligned: Align fragments to stream access points
    |   (improves seeking performance)
    | - mp4_include_pssh_in_stream: Include PSSH in stream for better
    |   DRM compatibility
    | - generate_static_live_mpd: Generate static MPD for DASH
    |   (improves caching)
    | - default_language: Default language for audio/subtitle tracks
    |
    */

    'packager_options' => (array) env('PLAYLIST_PACKAGER_OPTIONS', [
        'num_subsegments_per_sidx' => 0,
        'fragment_sap_aligned' => true,
        'mp4_include_pssh_in_stream' => true,
        'generate_static_live_mpd' => true,
        'default_language' => 'en',
    ]),

    /*
    |--------------------------------------------------------------------------
    | Shaka Streamer Options
    |--------------------------------------------------------------------------
    |
    | Configuration options for Shaka Streamer.
    | See: https://shaka-project.github.io/shaka-streamer/configuration_fields.html
    |
    */

    'streamer_options' => (array) env('PLAYLIST_STREAMER_OPTIONS', []),

];

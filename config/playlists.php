<?php

return [

    /**
     * This setting is used to define the disk where the playlists will be stored.
     * The disk should be configured in the `filesystems.php` configuration file.
     */
    'disk_name' => (string) env('PLAYLIST_DISK', 'segments'),

    /**
     * Default audio codecs to use for the playlist.
     * This can be overridden on a per-stream basis when adding streams to the playlist.
     */
    'audio_codecs' => (array) env('PLAYLIST_AUDIO_CODECS', ['aac']),

    /**
     * Default video codecs to use for the playlist.
     * This can be overridden on a per-stream basis when adding streams to the playlist.
     */
    'video_codecs' => (array) env('PLAYLIST_VIDEO_CODECS', ['hw:h264']),

    /**
     * This setting is used to define the resolutions that will be generated for the playlist.
     * The value is an array of strings, where each string represents a resolution (e.g., '1080p', '720p', '480p').
     */
    'resolutions' => (array) env('PLAYLIST_RESOLUTIONS', ['1080p', '720p', '480p']),

    /**
     * This setting is used to define the duration of each segment in the playlist, in seconds.
     * A typical value is between 4 and 10 seconds.
     */
    'segment_duration' => (int) env('PLAYLIST_SEGMENT_DURATION', 10),

    /**
     * This setting is used to define the encryption method for the playlist.
     *
     * - 'raw_key_encryption' → AES-128 (SAMPLE-AES, requires TS segments)
     * - 'clearkey' → W3C Clear Key EME (works with fMP4, browser-native)
     * - null → No encryption
     */
    'encryption' => (string) env('PLAYLIST_ENCRYPTION', 'raw_key_encryption'),

    /**
     * Protection scheme for encryption.
     *
     * - 'cenc' (AES-CTR) for Widevine/PlayReady/Clear Key - best for HLS with fMP4 and key rotation
     * - 'cbcs' (AES-CBC) for FairPlay/Safari - use with DASH, not HLS
     * - 'cbc1' legacy HLS, limited browser support
     * - null (SAMPLE-AES) widest compatibility with TS segments, NO key rotation support
     */
    'protection_scheme' => (string) env('PLAYLIST_PROTECTION_SCHEME', 'cenc'),

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
    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 24 * 14), // 14 days

    /**
     * Shaka Streamer options.
     *
     * @see https://shaka-project.github.io/shaka-streamer/configuration_fields.html
     */
    'streamer_options' => (array) env('PLAYLIST_STREAMER_OPTIONS', []),

];

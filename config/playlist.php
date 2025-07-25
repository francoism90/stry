<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Playlist Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are used to configure the playlist functionality.
    | You can enable or disable the playlist feature, set the disk for storage,
    | define the maximum disk usage, and set the expiration time for playlists.
    |
    */

    'enabled' => (bool) env('PLAYLIST_ENABLED', true),

    'disk_name' => env('PLAYLIST_DISK', 'transcodes'),

    'disk_max_usage' => (int) env('PLAYLIST_DISK_USAGE', 1073741824 * 100), // 100 GB

    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 8), // 8 hours

    /*
    |--------------------------------------------------------------------------
    | HLS Formats
    |--------------------------------------------------------------------------
    |
    | These settings are used to configure the HLS playlist formats.
    | You can define multiple formats with different bit rates.
    |
    */

    'hls_formats' => [
        ['name' => 'default', 'bit_rate' => 1500],
        // ['name' => 'low', 'bit_rate' => 500],
        // ['name' => 'mid', 'bit_rate' => 3000],
        // ['name' => 'high', 'bit_rate' => 6000],
        // ['name' => 'ultra', 'bit_rate' => 9000],
    ],

    'segment_length' => (int) env('PLAYLIST_SEGMENT_LENGTH', 10),

    'frame_interval' => (int) env('PLAYLIST_FRAME_INTERVAL', 48),

    'video_formats' => [
        \Support\FFMpeg\Format\Video\X264::class,
        \Support\FFMpeg\Format\Video\X265::class,
        \Support\FFMpeg\Format\Video\WebM::class,
    ],

    'prevent_transcoding' => (bool) env('PLAYLIST_PREVENT_TRANSCODING', true),

    'copy_video_codec' => (bool) env('PLAYLIST_COPY_VIDEO_CODEC', true),

    'copy_audio_codec' => (bool) env('PLAYLIST_COPY_AUDIO_CODEC', true),

    /*
    |--------------------------------------------------------------------------
    | Rotation Keys Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are used to configure the rotation keys for the playlist.
    | You can enable or disable the rotation keys, set the disk for storage,
    | define the maximum disk usage for rotation keys, and set the number of sections.
    |
    */

    'rotation_keys' => (bool) env('PLAYLIST_ROTATION_KEYS', true),

    'rotation_keys_sections' => (int) env('PLAYLIST_ROTATION_KEYS_SECTIONS', 10),

    'rotation_keys_disk' => env('PLAYLIST_ROTATION_KEYS_DISK', 'secrets'),

    'rotation_keys_disk_max_usage' => (int) env('PLAYLIST_ROTATION_DISK_USAGE', 1073741824 * 5), // 5 GB

    /*
    |--------------------------------------------------------------------------
    | Playlist Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware are applied to the routes that handle playlist requests.
    | They ensure that the user is authenticated and their email is verified.
    |
    */

    'middleware' => [
        'auth:sanctum',
        'verified',
        'subscribed',
        'cache:public;max_age=86400;immutable',
    ],

];

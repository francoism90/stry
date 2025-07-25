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

    /**
     * This setting is used to configure the HLS formats that will be used for the playlist.
     * You can define multiple formats with different bit rates.
     */

    'hls_formats' => [
        ['name' => 'default', 'bit_rate' => 1500],
        // ['name' => 'low', 'bit_rate' => 500],
        // ['name' => 'mid', 'bit_rate' => 3000],
        // ['name' => 'high', 'bit_rate' => 6000],
        // ['name' => 'ultra', 'bit_rate' => 9000],
    ],

    /**
     * This setting is used to configure the segment length for the playlist.
     * The segment length is the duration of each segment in seconds.
     * A shorter segment length will result in more segments, which can improve seeking performance,
     * but will also increase the file size.
     */

    'segment_length' => (int) env('PLAYLIST_SEGMENT_LENGTH', 10),

    /**
     * This setting is used to configure the frame interval for the playlist.
     * The frame interval is the number of frames between each keyframe in the video.
     * A lower frame interval will result in more keyframes, which can improve seeking performance,
     * but will also increase the file size.
     */

    'frame_interval' => (int) env('PLAYLIST_FRAME_INTERVAL', 48),

    /**
     * This setting is used to configure the video formats that will be used for transcoding.
     * You can define multiple formats that are supported by the FFMpeg library.
     * The order of the formats will determine the priority of transcoding.
     * The first format in the list will be used as the default format for transcoding.
     */

    'video_formats' => [
        \Support\FFMpeg\Format\Video\X264::class,
        \Support\FFMpeg\Format\Video\X265::class,
        \Support\FFMpeg\Format\Video\WebM::class,
    ],

    /**
     * When this setting is true, the playlist will not transcode the video files.
     * This will ignore any video format settings (bitrate, filters, etc.) and will use the original video file as is.
     * This is useful for cases where the original video file is already in a compatible format.
     */

    'prevent_transcoding' => (bool) env('PLAYLIST_PREVENT_TRANSCODING', true),

    /**
     * When this setting is true, the playlist will copy the video codec from the original video file.
     * This is useful for cases where the original video file is already in a compatible video codec.
     */

    'copy_video_codec' => (bool) env('PLAYLIST_COPY_VIDEO_CODEC', true),

    /**
     * When this setting is true, the playlist will copy the audio codec from the original video file.
     * This is useful for cases where the original video file is already in a compatible audio codec.
     */

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

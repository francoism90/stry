<?php

return [

    /**
     * This setting is used to define the disk where the playlist will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */

    'disk_name' => env('PLAYLIST_DISK', 'transcodes'),

    /**
     * This setting is used to define the maximum disk usage for the playlist.
     * The value is in bytes, and it will be used to limit the size of the playlist storage.
     */

    'disk_max_usage' => (int) env('PLAYLIST_DISK_USAGE', 1073741824 * 100), // 100 GB

    /**
     * This setting is used to define the expiration time for the playlist.
     * The value is in seconds, and it will be used to determine how long the playlist will be valid.
     * After this time, the playlist will be considered expired and will be prunable.
     */

    'expires_after' => (int) env('PLAYLIST_EXPIRES_AFTER', 60 * 60 * 8), // 8 hours

    /**
     * This setting is used to define the middleware that will be applied to the playlist routes.
     * You can add or remove middleware as needed.
     */

    'middleware' => [
        'auth:sanctum',
        'verified',
        'subscribed',
        'cache:public;max_age=86400;immutable',
    ],

    /**
     * This setting is used to configure the HLS formats that will be used for the playlist.
     * You can define multiple formats with different bit rates.
     * When prevent transcoding is enabled, the playlist will use the original video file as is,
     * and the bit rate will be ignored.
     */

    'hls_formats' => [
        ['name' => 'default', 'bit_rate' => 6000],
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
     * The order of the formats will determine the priority of transcoding.
     * The first format in the list will be used as the fallback format for transcoding.
     */

    'video_formats' => [
        \Support\FFMpeg\Format\Video\X264::class,
        \Support\FFMpeg\Format\Video\X265::class,
        \Support\FFMpeg\Format\Video\WebM::class,
    ],

    /**
     * When this setting is true, the playlist will copy the video codec from the original video file.
     * This is useful for cases where the original video file is already in a compatible video codec.
     */

    'copy_video_codec' => (bool) env('PLAYLIST_COPY_VIDEO_CODEC', true),

    /**
     * When this setting is true, the playlist will copy the audio codec from the original audio file.
     * This is useful for cases where the original video file is already in a compatible audio codec.
     */

    'copy_audio_codec' => (bool) env('PLAYLIST_COPY_AUDIO_CODEC', true),

    /**
     * When this setting is true, the playlist will prevent transcoding of the video file.
     * This will ignore any video format settings (bitrate, filters, etc.) and will use the original video file as is.
     * This is useful for cases where the original video file (audio + video codec) is already in a suitable format.
     */

    'prevent_transcoding' => (bool) env('PLAYLIST_PREVENT_TRANSCODING', true),

    /**
     * This setting is used to enable or disable the rotation keys (encryption) for playlists.
     */

    'rotation_keys' => (bool) env('PLAYLIST_ROTATION_KEYS', true),

    /**
     * This setting is used to define the number of sections for the rotation keys.
     * Each section will have its own key, which will be used to encrypt the segments of the playlist.
     * A lower number of sections will result in more keys, which can decrease performance.
     */

    'rotation_keys_sections' => (int) env('PLAYLIST_ROTATION_KEYS_SECTIONS', 10),

    /**
     * This setting is used to define the disk where the rotation keys will be stored.
     * You may want to use a temporary path to improve performance.
     * The disk should be configured in the `filesystems.php` configuration file.
     */

    'rotation_keys_disk' => env('PLAYLIST_ROTATION_KEYS_DISK', 'secrets'),

    /**
     * This setting is used to define the maximum disk usage for the rotation keys.
     * The value is in bytes, and it will be used to limit the size of the rotation keys storage.
     */

    'rotation_keys_disk_max_usage' => (int) env('PLAYLIST_ROTATION_DISK_USAGE', 1073741824 * 5), // 5 GB

];

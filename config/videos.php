<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Importing Videos
    |--------------------------------------------------------------------------
    |
    | This value determines the disk used when importing videos.
    | The disk should be configured in the filesystems configuration.
    |
    */

    'import_disk' => (string) env('VIDEO_IMPORT_DISK', 'import'),

    'import_batch_size' => (int) env('VIDEO_IMPORT_BATCH_SIZE', 20),

    /*
    |--------------------------------------------------------------------------
    | Playlist Creation
    |--------------------------------------------------------------------------
    |
    | This value determines whether a playlist should be automatically
    | created for each video. If true, playlists are created immediately,
    | allowing instant playback but requiring more processing and storage.
    |
    */

    'create_playlist' => (bool) env('VIDEO_CREATE_PLAYLIST', false),

    /*
    |--------------------------------------------------------------------------
    | Completion Threshold
    |--------------------------------------------------------------------------
    |
    | This value determines the percentage of a video that must be
    | watched before it is considered fully watched. Used to determine
    | when to reset progress to 0.
    |
    */

    'completion_threshold' => (float) env('VIDEO_COMPLETION_THRESHOLD', 0.95),

    /*
    |--------------------------------------------------------------------------
    | Transcode Settings
    |--------------------------------------------------------------------------
    |
    | This value determines the disk used for storing transcoded videos.
    | The disk should be configured in the filesystems configuration.
    | Additional settings for transcoding, such as hardware acceleration,
    | CRF, and preset, can also be configured here.
    |
    */

    'transcode_disk' => (string) env('VIDEO_TRANSCODE_DISK', 'transcodes'),

];

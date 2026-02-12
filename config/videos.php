<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Import Disk
    |--------------------------------------------------------------------------
    |
    | This value determines the disk used when importing videos.
    | The disk should be configured in the filesystems configuration.
    |
    */

    'import_disk' => (string) env('VIDEO_IMPORT_DISK', 'import'),

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
    | Import Batch Size
    |--------------------------------------------------------------------------
    |
    | This value determines the number of videos processed in a single
    | batch during import operations. Set based on your server's
    | capabilities to optimize performance and avoid timeouts.
    |
    */

    'import_batch_size' => (int) env('VIDEO_IMPORT_BATCH_SIZE', 20),

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

];

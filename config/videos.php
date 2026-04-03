<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disk Configuration
    |--------------------------------------------------------------------------
    |
    | The following options control which disks are used for video import and
    | transcoding operations. You can specify any disk defined in your
    | `filesystems.php` configuration file, or add new disks as needed.
    |
    */

    'import_disk' => (string) env('VIDEO_IMPORT_DISK', 'import'),

    'import_batch_size' => (int) env('VIDEO_IMPORT_BATCH_SIZE', 10),

    'transcode_disk' => (string) env('VIDEO_TRANSCODE_DISK', 'cache'),

    /*
    |--------------------------------------------------------------------------
    | Playlist Configuration
    |--------------------------------------------------------------------------
    |
    | The following options control playlist creation and completion thresholds.
    |
    */

    'create_playlists' => (bool) env('VIDEO_CREATE_PLAYLISTS', false),

    'completion_threshold' => (float) env('VIDEO_COMPLETION_THRESHOLD', 0.97),

];

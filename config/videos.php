<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Import Disk
    |--------------------------------------------------------------------------
    |
    | This value determines the disk that will be used when importing videos.
    | The disk should be configured in the filesystems configuration file.
    |
    */

    'import_disk' => (string) env('VIDEO_IMPORT_DISK', 'import'),

    /*
    |--------------------------------------------------------------------------
    | Import Batch Size
    |--------------------------------------------------------------------------
    |
    | This value determines the number of videos that will be processed
    | in a single batch during import operations.
    |
    */

    'import_batch_size' => (int) env('VIDEO_IMPORT_BATCH_SIZE', 20),

    /*
    |--------------------------------------------------------------------------
    | Completion Threshold
    |--------------------------------------------------------------------------
    | This value determines the percentage of a video that must be watched
    | before it is considered as fully watched. This is used to determine when to reset progress to 0.
    |
    */

    'completion_threshold' => (float) env('VIDEO_COMPLETION_THRESHOLD', 0.95),

];

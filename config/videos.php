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

    'import_disk' => env('VIDEO_IMPORT_DISK', 'import'),

    /*
    |--------------------------------------------------------------------------
    | Import Batch Size
    |--------------------------------------------------------------------------
    |
    | This value determines the number of videos that will be processed
    | in a single batch during import operations.
    |
    */

    'import_batch_size' => env('VIDEO_IMPORT_BATCH_SIZE', 100),

];

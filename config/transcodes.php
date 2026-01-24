<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Encoder
    |--------------------------------------------------------------------------
    |
    | The default encoder to use for transcoding operations.
    |
    */

    'default' => env('TRANSCODE_DEFAULT_ENCODER', 'ab-av1'),

    /*
    |--------------------------------------------------------------------------
    | Transcodes Disk
    |--------------------------------------------------------------------------
    |
    | The disk where transcoded files will be temporarily stored before
    | replacing the original media files.
    |
    */

    'disk' => env('TRANSCODE_DISK', 'transcodes'),

];

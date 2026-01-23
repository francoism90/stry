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

    /*
    |--------------------------------------------------------------------------
    | Encoding Configurations
    |--------------------------------------------------------------------------
    |
    | Define encoder configurations with their encoding options.
    | Each encoder contains specific settings for the encoding process.
    |
    */

    'encoders' => [
        'ab-av1' => [
            'preset' => env('TRANSCODE_AV1_PRESET', '6'),
            'min_vmaf' => env('TRANSCODE_AV1_MIN_VMAF', 85),
            'max_encoded_percent' => env('TRANSCODE_AV1_MAX_PERCENT', 300),
        ],
    ],

];

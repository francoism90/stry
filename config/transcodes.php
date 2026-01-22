<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | The default preset to use for transcoding operations.
    |
    */

    'default' => env('TRANSCODE_DEFAULT_PRESET', 'ab-av1'),

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
    | Transcoding Presets
    |--------------------------------------------------------------------------
    |
    | Define transcoding presets with their encoding options.
    | Each preset contains encoder-specific settings.
    |
    */

    'presets' => [
        'ab-av1' => [
            'preset' => env('TRANSCODE_AV1_PRESET', '6'),
            'min_vmaf' => env('TRANSCODE_AV1_MIN_VMAF', 90),
            'max_encoded_percent' => env('TRANSCODE_AV1_MAX_PERCENT', 150),
        ],
    ],

];

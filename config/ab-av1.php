<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ab-av1
|--------------------------------------------------------------------------
|
| Temporary files, logging, disks and the ffmpeg and ffprobe that ab-av1
| runs come from laravel-media's config/media.php.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Binary
    |--------------------------------------------------------------------------
    |
    | The ab-av1 binary: a name looked up in the PATH, or a full path such as
    | /home/user/.cargo/bin/ab-av1. "php artisan media:info" shows what was found.
    |
    */

    'binary' => env('AB_AV1_BINARY', 'ab-av1'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The seconds one ab-av1 command may run. Encoding a long video can take
    | hours, so keep this at or below your queue job's $timeout.
    |
    */

    'timeout' => (int) env('AB_AV1_TIMEOUT', 14400),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Applied to every command; the builder's methods override them. Leave an
    | option null to use ab-av1's own default.
    |
    | preset: encoder speed, for svt-av1 from 0 (slowest) to 13 (fastest)
    | min_vmaf: the VMAF score auto-encode and crf-search aim for
    | max_encoded_percent: fail when the encode would be larger than this
    |     percentage of the input
    | samples: how many samples crf-search encodes
    | encoder: ffmpeg's encoder, e.g. "av1_vaapi" (ab-av1 uses libsvtav1)
    | encoder_args: space-separated key=value encoder arguments (--enc)
    | pix_format: the pixel format, e.g. "yuv420p10le"
    | video_filter: an ffmpeg video filter chain, e.g. "scale=1280:-2"
    | ffmpeg_input_options: space-separated key=value input options
    |     (--enc-input), e.g. "hwaccel=vaapi hwaccel_output_format=vaapi"
    |
    */

    'preset' => env('AB_AV1_PRESET', 6),

    'min_vmaf' => env('AB_AV1_MIN_VMAF', 94),

    'max_encoded_percent' => env('AB_AV1_MAX_ENCODED_PERCENT', 300),

    'samples' => env('AB_AV1_SAMPLES'),

    'encoder' => env('AB_AV1_ENCODER'),

    'encoder_args' => env('AB_AV1_ENCODER_ARGS'),

    'pix_format' => env('AB_AV1_PIX_FORMAT'),

    'video_filter' => env('AB_AV1_VIDEO_FILTER'),

    'ffmpeg_input_options' => env('AB_AV1_FFMPEG_INPUT_OPTIONS'),

];

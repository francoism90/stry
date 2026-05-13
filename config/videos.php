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

    'completion_threshold' => (float) env('VIDEO_COMPLETION_THRESHOLD', 0.98),

    /*
    |--------------------------------------------------------------------------
    | Similar Video Matching Configuration
    |--------------------------------------------------------------------------
    |
    | The following options control the behavior of similar video matching algorithms.
    |
    */

    'common_words' => [
        // Base stop words.
        'a', 'an', 'the', 'and', 'or', 'of', 'in', 'to',

        // Generic media terms.
        'movie', 'film', 'show', 'tv', 'series', 'episode', 'season', 'part',
        'chapter', 'volume', 'official',

        // Content and edition terms.
        'clip', 'scene', 'scenes', 'trailer', 'teaser', 'commentary',
        'behind', 'making', 'deleted', 'bloopers', 'recap', 'edition',
        'extended', 'uncut', 'dubbed', 'subbed', 'subtitled',

        // Franchise / release naming terms.
        'remake', 'reboot', 'sequel', 'prequel', 'pilot', 'finale', 'cast',

        // Quality and source tags.
        'full', 'hd', 'fhd', 'uhd', 'hdr', '4k', '720p', '1080p', '2160p',
        'hq', 'x264', 'x265', 'bluray', 'webrip', 'webdl',
    ],

];

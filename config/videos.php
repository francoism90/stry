<?php

return [

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

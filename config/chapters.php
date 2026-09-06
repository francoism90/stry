<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Type Classification Patterns
    |--------------------------------------------------------------------------
    |
    | When a chapter is created or updated without an explicit type, its free
    | text label is matched against these regular expressions, in order, to
    | classify it (e.g. "Introduction" or "Leader" both classify as intro).
    | The first pattern that matches wins; an explicit type is never
    | overwritten by this classification.
    |
    */

    'patterns' => [
        'intro' => (string) env('CHAPTERS_INTRO_PATTERN', '/\b(intro(duction)?|leader|opening)\b/i'),
        'recap' => (string) env('CHAPTERS_RECAP_PATTERN', '/\b(recap|previously|catch[- ]?up)\b/i'),
        'credits' => (string) env('CHAPTERS_CREDITS_PATTERN', '/\b(credits?|end\s?card|outro)\b/i'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Type
    |--------------------------------------------------------------------------
    |
    | The type assigned to a chapter when its label matches none of the
    | patterns above.
    |
    */

    'default_type' => (string) env('CHAPTERS_DEFAULT_TYPE', 'scene'),

];

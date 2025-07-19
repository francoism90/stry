<?php

use Domain\Groups\Models\Group;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => true,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => true,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://www.meilisearch.com/docs/learn/configuration/instance_options#all-instance-options
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            User::class => [
                'filterableAttributes' => [
                    'id',
                    'created_at',
                    'updated_at',
                    '__soft_deleted',
                ],

                'searchableAttributes' => [
                    'name',
                    'email',
                ],

                'sortableAttributes' => [
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ],

            Group::class => [
                'filterableAttributes' => [
                    'id',
                    'kind',
                    'type',
                    'created_at',
                    'updated_at',
                    '__soft_deleted',
                ],

                'searchableAttributes' => [
                    'name',
                    'content',
                ],

                'sortableAttributes' => [
                    'id',
                    'name',
                    'kind',
                    'type',
                    'created_at',
                    'updated_at',
                ],

                'typoTolerance' => [
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],

                'pagination' => [
                    'maxTotalHits' => 32000,
                ],
            ],

            Video::class => [
                'filterableAttributes' => [
                    'id',
                    'identifier',
                    'season',
                    'episode',
                    'part',
                    'adult',
                    'caption',
                    'tagged',
                    'created_at',
                    'updated_at',
                    '__soft_deleted',
                ],

                'searchableAttributes' => [
                    'title',
                    'name',
                    'identifier',
                    'season',
                    'episode',
                    'part',
                    'tags',
                    'relatables',
                    'content',
                    'summary',
                    'released',
                ],

                'sortableAttributes' => [
                    'id',
                    'name',
                    'identifier',
                    'title',
                    'season',
                    'episode',
                    'part',
                    'released',
                    'duration',
                    'created_at',
                    'updated_at',
                ],

                'synonyms' => [
                    '0' => ['00', '000'],
                    '1' => ['01', '001'],
                    '2' => ['02', '002'],
                    '3' => ['03', '003'],
                    '4' => ['04', '004'],
                    '5' => ['05', '005'],
                    '6' => ['06', '006'],
                    '7' => ['07', '007'],
                    '8' => ['08', '008'],
                    '9' => ['09', '009'],
                    '&' => ['and'],
                    '@' => ['at'],
                    '#' => ['hash', 'hashtag', 'tag'],
                ],

                'stopWords' => [
                    '.',
                    ',',
                    '-',
                    '_',
                    '-',
                    '|',
                    '&',
                    '/',
                    '(',
                    ')',
                    '[',
                    ']',
                ],

                'rankingRules' => [
                    'sort',
                    'words',
                    'attribute',
                    'typo',
                    'proximity',
                    'exactness',
                ],

                'typoTolerance' => [
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],

                'pagination' => [
                    'maxTotalHits' => 32000,
                ],
            ],

            Tag::class => [
                'filterableAttributes' => [
                    'id',
                    'type',
                    'adult',
                    'related',
                    'created_at',
                    'updated_at',
                    '__soft_deleted',
                ],

                'searchableAttributes' => [
                    'name',
                    'description',
                    'synonyms',
                    'type',
                ],

                'sortableAttributes' => [
                    'id',
                    'name',
                    'type',
                    'adult',
                    'order',
                    'created_at',
                    'updated_at',
                ],

                'synonyms' => [
                    '0' => ['00', '000'],
                    '1' => ['01', '001'],
                    '2' => ['02', '002'],
                    '3' => ['03', '003'],
                    '4' => ['04', '004'],
                    '5' => ['05', '005'],
                    '6' => ['06', '006'],
                    '7' => ['07', '007'],
                    '8' => ['08', '008'],
                    '9' => ['09', '009'],
                    '&' => ['and'],
                    '@' => ['at'],
                    '#' => ['hash', 'hashtag', 'tag'],
                ],

                'stopWords' => [
                    '.',
                    ',',
                    '-',
                    '_',
                    '-',
                    '|',
                    '&',
                    '/',
                    '(',
                    ')',
                    '[',
                    ']',
                ],

                'rankingRules' => [
                    'sort',
                    'words',
                    'attribute',
                    'typo',
                    'proximity',
                    'exactness',
                ],

                'typoTolerance' => [
                    'minWordSizeForTypos' => [
                        'oneTypo' => 3,
                        'twoTypos' => 5,
                    ],
                ],

                'pagination' => [
                    'maxTotalHits' => 32000,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Typesense Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Typesense settings. Typesense is an open
    | source search engine using minimal configuration. Below, you will
    | state the host, key, and schema configuration for the instance.
    |
    */

    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),

            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],

            'nearest_node' => [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'path' => env('TYPESENSE_PATH', ''),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],

            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
            'healthcheck_interval_seconds' => env('TYPESENSE_HEALTHCHECK_INTERVAL_SECONDS', 30),
            'num_retries' => env('TYPESENSE_NUM_RETRIES', 3),
            'retry_interval_seconds' => env('TYPESENSE_RETRY_INTERVAL_SECONDS', 1),
        ],

        'model-settings' => [
            // User::class => [
            //     'collection-schema' => [
            //         'fields' => [
            //             [
            //                 'name' => 'id',
            //                 'type' => 'string',
            //             ],
            //             [
            //                 'name' => 'name',
            //                 'type' => 'string',
            //             ],
            //             [
            //                 'name' => 'created_at',
            //                 'type' => 'int64',
            //             ],
            //         ],
            //         'default_sorting_field' => 'created_at',
            //     ],
            //     'search-parameters' => [
            //         'query_by' => 'name'
            //     ],
            // ],
        ],
    ],

];

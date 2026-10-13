<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | DDD Substitutions
    |--------------------------------------------------------------------------
    |
    | Override or add to the type-to-subfolder mapping used by ddd:make,
    | e.g. to point "action" at a different subfolder than "Actions".
    |
    */

    'substitutions' => env('DDD_SUBSTITUTIONS', [
        // 'action' => 'CustomActions',
    ]),

    /*
    |--------------------------------------------------------------------------
    | DDD Stub Overrides
    |--------------------------------------------------------------------------
    |
    | Map a type to a stub file when it should not come from
    | base_path("stubs/{type}.ddd.stub") or the package's bundled stub.
    | Relative paths resolve from the application's base path.
    |
    */

    'stubs' => env('DDD_STUBS', [
        // 'action' => 'stubs/ddd/custom-action.stub',
    ]),

    /*
    |--------------------------------------------------------------------------
    | Event Auto Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, Laravel's event listener auto discovery resolves class
    | names using each layer's namespace and path below, instead of assuming
    | everything lives under app_path(). This lets listeners under Domain,
    | Modules, or any other layer be discovered automatically.
    |
    | Set it to true for every layer, false to disable it, or a list (or
    | comma-separated string) of layer keys, e.g. ['Domain', 'Modules'].
    |
    */

    'discover_events' => env('DDD_DISCOVER_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Policy Auto Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, Laravel's policy auto discovery also looks for policies
    | next to each layer's models, e.g. Domain\Users\Models\User resolves to
    | Domain\Users\Policies\UserPolicy, before Laravel's default guesses.
    | Models outside a Models folder resolve to {Layer}\{PluralModel}\Policies,
    | which only applies to Domain unless you list the layers yourself.
    |
    | Set it to true for every layer, false to disable it, or a list (or
    | comma-separated string) of layer keys, e.g. ['Domain', 'Modules'].
    |
    */

    'discover_policies' => env('DDD_DISCOVER_POLICIES', true),

    /*
    |--------------------------------------------------------------------------
    | Layers
    |--------------------------------------------------------------------------
    |
    | Each layer maps a namespace to a path. ddd:install registers these in
    | composer.json. Change "namespace" to nest a layer under App\ instead,
    | e.g. App\Modules, if you prefer.
    |
    */

    'layers' => [

        // Framework-agnostic business logic.
        'Domain' => [
            'namespace' => env('DDD_DOMAIN_NAMESPACE', 'Domain'),
            'path' => env('DDD_DOMAIN_PATH', 'src/Domain'),
        ],

        // Laravel-facing code (controllers, requests, middleware) that orchestrates Domain.
        'Modules' => [
            'namespace' => env('DDD_MODULES_NAMESPACE', 'Modules'),
            'path' => env('DDD_MODULES_PATH', 'src/Modules'),
        ],

        // Base classes, core providers, and shared helpers.
        'Foundation' => [
            'namespace' => env('DDD_FOUNDATION_NAMESPACE', 'Foundation'),
            'path' => env('DDD_FOUNDATION_PATH', 'src/Foundation'),
        ],

        // Generic, cross-cutting helpers that don't belong to a specific layer.
        'Support' => [
            'namespace' => env('DDD_SUPPORT_NAMESPACE', 'Support'),
            'path' => env('DDD_SUPPORT_PATH', 'src/Support'),
        ],

    ],

];

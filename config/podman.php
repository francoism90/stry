<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Services Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix used for Podman quadlet files. The default value
    | is the application name, which can be overruled when needed.
    |
    */

    'quadlet_prefix' => env('PODMAN_QUADLET_PREFIX', env('APP_NAME', 'laravel')),

    'proxy_prefix' => env('PODMAN_PROXY_PREFIX', 'proxy'),

    /*
    |--------------------------------------------------------------------------
    | Quadlets Template Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where Podman quadlet and runtime templates are located.
    | If the path does not exist, it will fallback to the vendor provided templates.
    */

    'quadlets_path' => env('PODMAN_QUADLETS_PATH', 'containers/quadlets'),

    'runtimes_path' => env('PODMAN_RUNTIMES_PATH', 'containers/runtimes'),

    /*
    |--------------------------------------------------------------------------
    | Working Path
    |--------------------------------------------------------------------------
    |
    | The host path baked into the "{{base-path}}"/"{{runtime-path}}"/
    | "{{config-path}}" placeholders. It does not affect where Artisan
    | itself reads or writes files — that's always base_path(). Defaults
    | to base_path(); override when rendering happens somewhere whose
    | filesystem view of the project isn't the host's, e.g. inside the
    | container used by "Setting up without PHP on the host".
    |
    */

    'working_path' => env('PODMAN_WORKING_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Service UID and GID
    |--------------------------------------------------------------------------
    |
    | These values determine the UID and GID used for Podman quadlet files.
    | The default values are null, which means the system's UID and GID
    | will be used. These can be overruled when needed.
    |
    */

    'quadlet_uid' => env('PODMAN_QUADLET_UID'),

    'quadlet_gid' => env('PODMAN_QUADLET_GID'),

    /*
    |--------------------------------------------------------------------------
    | Podman Runtimes Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where Podman runtimes are placed. The default value
    | is 'runtimes', which can be overruled when needed.
    |
    */

    'runtime_path' => env('PODMAN_RUNTIME_PATH', 'runtimes'),

    /*
    |--------------------------------------------------------------------------
    | Podman Config Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where Podman configuration files are placed.
    | The default value is 'runtimes', which can be overruled when needed.
    |
    */

    'config_path' => env('PODMAN_CONFIG_PATH', 'runtimes'),

    /*
    |--------------------------------------------------------------------------
    | Publish Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where "podman:setup"/"podman:install" always
    | render a service's ".quadlets" file, before installing it. Actually
    | installing is skipped when --no-install is passed or the "podman"
    | binary can't be found, leaving the rendered file as the only output —
    | run "podman quadlet install" against it on the host to finish.
    |
    */

    'publish_path' => env('PODMAN_PUBLISH_PATH', 'storage/app/podman'),

    /*
    |--------------------------------------------------------------------------
    | SELinux Volume Mapping
    |--------------------------------------------------------------------------
    |
    | This value determines whether the Z, z and U SELinux volume flags are
    | kept on Volume= entries. Disable this on hosts that do not run SELinux,
    | since these flags are rejected there.
    |
    */

    'selinux_volume_mapping' => env('PODMAN_SELINUX_VOLUME_MAPPING', true),

    /*
    |--------------------------------------------------------------------------
    | Reload Systemd
    |--------------------------------------------------------------------------
    |
    | This value determines whether to reload systemd after installing a quadlet.
    | The default value is true, which means that systemd will be reloaded after installation.
    |
    */

    'reload_systemd' => env('PODMAN_RELOAD_SYSTEMD', true),

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | This value lists the services installed by the "podman:setup" command
    | when no explicit service is given. Any service not listed here can
    | still be installed manually with "podman:install". Accepts either a
    | comma-separated string (handy for the PODMAN_DEFAULT_SERVICES env
    | variable) or a plain array of service names.
    |
    */

    'services' => env('PODMAN_DEFAULT_SERVICES', [
        'proxy',
        'app',
        'pgsql',
        'valkey',
        'horizon',
        'reverb',
        'schedule',
        'mailpit',
        'rustfs',
        'typesense',
        'inertia-ssr',
    ]),

    /*
    |--------------------------------------------------------------------------
    | Runtimes
    |--------------------------------------------------------------------------
    |
    | This value lists the runtimes published by the "podman:setup" command
    | when no explicit runtime is given. Accepts either a comma-separated
    | string (handy for the PODMAN_DEFAULT_RUNTIMES env variable) or a
    | plain array of runtime names.
    |
    */

    'runtimes' => env('PODMAN_DEFAULT_RUNTIMES', [
        'frankenphp-octane',
        'proxy',
        's3',
    ]),

    /*
    |--------------------------------------------------------------------------
    | S3 Buckets
    |--------------------------------------------------------------------------
    |
    | These values list the S3 buckets created by the "podman:s3-setup" command,
    | and which of them should receive the CORS policy published with the "s3"
    | runtime (see "podman:publish s3"). Accepts either a comma-separated string
    | (handy for the PODMAN_S3_BUCKETS / PODMAN_S3_CORS_BUCKETS env variables)
    | or a plain array of bucket names.
    |
    */

    's3_buckets' => env('PODMAN_S3_BUCKETS', [
        'local',
        'conversions',
        'segments',
        'secrets',
    ]),

    's3_cors_buckets' => env('PODMAN_S3_CORS_BUCKETS', [
        'conversions',
        'segments',
        'secrets',
    ]),
];

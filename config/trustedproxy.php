<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Whose X-Forwarded-For, -Port and -Proto headers stry believes: addresses
    | or networks, separated by commas. The Pods proxy reaches it from a
    | private address, through Podman's network or the on-demand socket.
    | X-Forwarded-Host is never trusted.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', 'private_ranges'),

];

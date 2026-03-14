<?php

declare(strict_types=1);

use Foundation\Providers\AppServiceProvider;
use Foundation\Providers\AuthServiceProvider;
use Foundation\Providers\BroadcastServiceProvider;
use Foundation\Providers\EventServiceProvider;
use Foundation\Providers\FortifyServiceProvider;
use Foundation\Providers\HorizonServiceProvider;
use Foundation\Providers\RouteServiceProvider;
use Foundation\Providers\ScoutServiceProvider;
use Foundation\Providers\ViewServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    BroadcastServiceProvider::class,
    EventServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    RouteServiceProvider::class,
    ScoutServiceProvider::class,
    ViewServiceProvider::class,
];

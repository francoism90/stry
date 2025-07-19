<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureApiRoutes();
        $this->configureWebRoutes();
    }

    protected function configureApiRoutes(): void
    {
        Broadcast::routes([
            'prefix' => 'api/v1',
            'middleware' => ['api', 'auth:sanctum'],
        ]);
    }

    protected function configureWebRoutes(): void
    {
        Broadcast::routes([
            'middleware' => ['web'],
        ]);
    }
}

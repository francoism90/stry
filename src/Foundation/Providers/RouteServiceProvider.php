<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureResourceParameters();
        $this->configureRoutePatterns();
    }

    protected function configureResourceParameters(): void
    {
        Route::resourceParameters([
            'collections' => 'group',
            'media' => 'media',
        ]);
    }

    protected function configureRoutePatterns(): void
    {
        Route::pattern('query', '.*');
    }
}

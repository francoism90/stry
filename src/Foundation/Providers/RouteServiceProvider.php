<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::resourceParameters([
            'collections' => 'group',
            'media' => 'media',
        ]);
    }
}

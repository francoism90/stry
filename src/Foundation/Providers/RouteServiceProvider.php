<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimits();
        $this->configureResourceParameters();
        $this->configureRoutePatterns();
    }

    protected function configureRateLimits(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->getKey())
                : Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('vod', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(240)->by($request->user()->getKey())
                : Limit::perMinute(240)->by($request->ip());
        });
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

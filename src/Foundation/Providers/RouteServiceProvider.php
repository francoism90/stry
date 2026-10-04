<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Videos\Actions\CreateVideoDirectStream;
use Domain\Videos\Models\Video;
use Foxws\Media\Delivery\DirectStream;
use Foxws\Media\Facades\MediaStream;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $this->configureMediaStreams();
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

    protected function configureMediaStreams(): void
    {
        MediaStream::define('videos', function (Video $video, CreateVideoDirectStream $action): DirectStream {
            Gate::authorize('view', $video);

            return $action->handle($video);
        })->signed();
    }
}

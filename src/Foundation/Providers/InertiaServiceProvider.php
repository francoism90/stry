<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;

class InertiaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if (in_array($response->statusCode(), [403, 404, 419, 500, 503])) {
                return $response->render('Errors/ApplicationError', [
                    'status' => $response->statusCode(),
                ])->withSharedData();
            }
        });
    }
}

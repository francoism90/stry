<?php

declare(strict_types=1);

use Foundation\Http\Middleware\AddCspHeaders;
use Foundation\Http\Middleware\AddHorizonCspNonce;
use Foundation\Http\Middleware\AddTelescopeCspNonce;
use Foundation\Http\Middleware\SetCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Modules\Web\Profiles\Middleware\ResolveCurrentProfile;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use Support\Inertia\Middleware\HandleInertiaRequests;

$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Inertia action routes (web middleware for session, CSRF, and Inertia handling)
            Route::middleware('web')
                ->prefix('/actions')
                ->name('actions.')
                ->group(base_path('routes/actions.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Proxies configuration
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        // Global middleware aliases for convenient usage in routes and controllers
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'cache' => SetCacheHeaders::class,
            'cache.bypass' => DoNotCacheResponse::class,
            'csp.horizon' => AddHorizonCspNonce::class,
            'csp.telescope' => AddTelescopeCspNonce::class,
            'precognitive' => HandlePrecognitiveRequests::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'role' => RoleMiddleware::class,
        ]);

        // Configure web middleware
        $middleware->web(append: [
            AddCspHeaders::class,
            ResolveCurrentProfile::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Configure API middleware
        $middleware->api(append: [
            CacheResponse::class,
        ]);

        // Sanctum middleware for API authentication and rate limiting
        $middleware->statefulApi();
        $middleware->throttleWithRedis();
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();

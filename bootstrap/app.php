<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Support\Inertia\Middlewares\HandleInertiaRequests;
use Symfony\Component\HttpFoundation\Response;

$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

$app = Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Global resource parameter mappings
            Route::resourceParameters([
                'media' => 'media',
            ]);

            // Rate limiting
            RateLimiter::for('api', function (Request $request) {
                return $request->user()
                    ? Limit::perMinute(120)->by($request->user()->getKey())
                    : Limit::perMinute(20)->by($request->ip());
            });

            RateLimiter::for('none', function (Request $request) {
                return Limit::none();
            });

            // Admin Routes
            Route::middleware(['web', 'verified', 'role:super-admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->statefulApi();
        $middleware->throttleWithRedis();
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'cache' => \Foundation\Http\Middlewares\SetCacheHeaders::class,
            'cache_response' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,
            'private' => \Foundation\Http\Middlewares\EnsureRequestHasPrivateSubnet::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'subscribed' => \App\Api\Users\Middlewares\EnsureUserHasSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                return Inertia::render('Errors/ApplicationError', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            } elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })
    ->withEvents(discover: [
        base_path('src/Domain/*/Listeners'),
    ])
    ->withCommands([
        \Domain\Groups\Commands\ClearCommand::class,
        \Domain\Playlists\Commands\ClearCommand::class,
        \Domain\Tags\Commands\CreateCommand::class,
        \Domain\Tags\Commands\SortCommand::class,
        \Domain\Users\Commands\CreateCommand::class,
        \Domain\Videos\Commands\ClearCommand::class,
        \Domain\Videos\Commands\ImportCommand::class,
        \Support\Scout\Commands\SyncCommand::class,
    ])
    ->create();

$app->useAppPath($basePath.'/src/App');

return $app;

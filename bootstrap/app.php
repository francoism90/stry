<?php

declare(strict_types=1);

use App\Web\Profiles\Middlewares\ResolveCurrentProfile;
use Domain\Groups\Commands\ClearGroupCommand;
use Domain\Playlists\Commands\ClearPlaylistCommand;
use Domain\Tags\Commands\CreateTagCommand;
use Domain\Tags\Commands\SortTagsCommand;
use Domain\Transcodes\Commands\ClearTranscodeCommand;
use Domain\Transcodes\Commands\CreateTranscodeCommand;
use Domain\Transcodes\Commands\ImportTranscodeCommand;
use Domain\Users\Commands\CreateUserCommand;
use Domain\Videos\Commands\ClearVideoCommand;
use Domain\Videos\Commands\ImportVideoCommand;
use Foundation\Http\Middlewares\AddCspHeaders;
use Foundation\Http\Middlewares\EnsureRequestHasPrivateSubnet;
use Foundation\Http\Middlewares\SetCacheHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use Support\Inertia\Middlewares\HandleInertiaRequests;
use Support\Scout\Commands\SyncScoutCommand;

$basePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

$app = Application::configure(basePath: $basePath)
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
            'private' => EnsureRequestHasPrivateSubnet::class,
            'precognitive' => HandlePrecognitiveRequests::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'role' => RoleMiddleware::class,
        ]);

        // Add Inertia middleware globally to ensure proper handling of Inertia requests and asset preloading
        $middleware->web(append: [
            AddCspHeaders::class,
            ResolveCurrentProfile::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Sanctum middleware for API authentication and rate limiting
        $middleware->statefulApi();
        $middleware->throttleWithRedis();
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withEvents(discover: [
        base_path('src/Domain/*/Listeners'),
    ])
    ->withCommands([
        ClearGroupCommand::class,
        ClearPlaylistCommand::class,
        ClearTranscodeCommand::class,
        ImportTranscodeCommand::class,
        CreateTagCommand::class,
        SortTagsCommand::class,
        CreateTranscodeCommand::class,
        CreateUserCommand::class,
        ClearVideoCommand::class,
        ImportVideoCommand::class,
        SyncScoutCommand::class,
    ])
    ->create();

$app->useAppPath($basePath.'/src/App');

return $app;

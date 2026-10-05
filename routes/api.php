<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Api\Authentication\Controllers\HomeController;
use Modules\Api\Tags\Controllers\TagController;
use Modules\Api\Videos\Controllers\VideoSessionController;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');

    // VOD - Direct play
    Route::withoutMiddleware('throttle:api')
        ->middleware(['throttle:vod', 'cache.bypass'])
        ->group(fn () => Route::mediaStream('direct/{video}', 'videos'));

    // VOD - Analytics
    Route::post('/record/{video}', VideoSessionController::class)
        ->withoutMiddleware('throttle:api')
        ->name('play.session');
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Api\Authentication\Controllers\HomeController;
use Modules\Api\Playlists\Controllers\PlaylistManifestController;
use Modules\Api\Tags\Controllers\TagController;
use Modules\Api\Videos\Controllers\VideoSessionController;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');

    // VOD - Playlists
    Route::get('/play/{playlist}/{path}', PlaylistManifestController::class)
        ->withoutMiddleware('throttle:api')
        ->where('path', '.*')
        ->name('play.manifest');

    // VOD - Analytics
    Route::post('/record/{video}', VideoSessionController::class)
        ->withoutMiddleware('throttle:api')
        ->name('play.session');
});

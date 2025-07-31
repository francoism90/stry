<?php

declare(strict_types=1);

use App\Api\Authentication\Controllers\HomeController;
use App\Api\Playlists\Controllers\PlaylistKeyController;
use App\Api\Playlists\Controllers\PlaylistManifestController;
use App\Api\Tags\Controllers\TagController;
use App\Api\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Videos
    Route::apiResource('videos', VideoController::class);

    // Tags
    Route::apiResource('tags', TagController::class);

    // Playlists
    Route::name('playlists.')->prefix('play')->group(function () {
        Route::get('/{playlist}/key/{path}', PlaylistKeyController::class)->name('key');
        Route::get('/{playlist}/playlist/{path}', PlaylistManifestController::class)->name('playlist');
    });
});

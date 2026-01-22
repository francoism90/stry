<?php

declare(strict_types=1);

use App\Api\Authentication\Controllers\HomeController;
use App\Api\Playlists\Controllers\ClearKeyLicenseController;
use App\Api\Playlists\Controllers\PlaylistManifestController;
use App\Api\Playlists\Controllers\PlaylistSessionController;
use App\Api\Tags\Controllers\TagController;
use App\Api\Videos\Controllers\VideoGroupController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');

    // Playlists
    Route::name('playlists.')->prefix('play')->group(function () {
        Route::get('/{playlist}/playlist/{path}', PlaylistManifestController::class)->name('playlist');
        Route::post('/{playlist}/license', ClearKeyLicenseController::class)->name('license');
        Route::post('/{playlist}/session', PlaylistSessionController::class)->name('session');
    });

    // Videos
    Route::name('videos.')->prefix('videos')->group(function () {
        Route::post('/{video}/groups/{type}', VideoGroupController::class)->name('group');
    });
});

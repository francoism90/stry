<?php

declare(strict_types=1);

use App\Api\Authentication\Controllers\HomeController;
use App\Api\Playlists\Controllers\PlaylistManifestController;
use App\Api\Playlists\Controllers\PlaylistSegmentController;
use App\Api\Playlists\Controllers\PlaylistSessionController;
use App\Api\Tags\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');

    // Playlists
    Route::name('playlists.')->prefix('play')->group(function () {
        Route::get('/{playlist}/playlist/{path}', PlaylistManifestController::class)
            ->where('path', '.*')
            ->name('playlist');
        Route::get('/{playlist}/segment/{path}', PlaylistSegmentController::class)
            ->where('path', '.*')
            ->name('segment');
        Route::post('/{playlist}/session', PlaylistSessionController::class)->name('session');
    });
});

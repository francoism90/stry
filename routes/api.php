<?php

declare(strict_types=1);

use App\Api\Authentication\Controllers\HomeController;
use App\Api\Playlists\Controllers\PlaylistManifestController;
use App\Api\Playlists\Controllers\PlaylistSessionController;
use App\Api\Tags\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');

    // VOD - Playlists
    Route::get('/play/{playlist}/{path}', PlaylistManifestController::class)
        ->where('path', '.*')
        ->name('play.manifest');

    // VOD - Analytics
    Route::post('/record/{playlist}', PlaylistSessionController::class)->name('play.session');
});

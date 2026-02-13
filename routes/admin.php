<?php

declare(strict_types=1);

use App\Admin\Dashboard\Controllers\HomeController;
use App\Admin\Playlists\Controllers\PlaylistController;
use App\Admin\Tags\Controllers\TagController;
use App\Admin\Tags\Controllers\TagOrderController;
use App\Admin\Users\Controllers\UserController;
use App\Admin\Videos\Controllers\VideoController;
use App\Admin\Videos\Controllers\VideoImportController;
use App\Admin\Videos\Controllers\VideoMediaController;
use App\Admin\Videos\Controllers\VideoPlaylistController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Interactions
Route::post('tags/reorder', TagOrderController::class)->name('tags.reorder');
Route::post('videos/import', VideoImportController::class)->name('videos.import');

// Users
Route::resource('users', UserController::class);

// Playlists
Route::resource('playlists', PlaylistController::class);

// Tags
Route::resource('tags', TagController::class);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class);
Route::resource('videos.playlists', VideoPlaylistController::class);

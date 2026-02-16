<?php

declare(strict_types=1);

use App\Admin\Dashboard\Controllers\HomeController;
use App\Admin\Playlists\Controllers\PlaylistController;
use App\Admin\Tags\Controllers\TagController;
use App\Admin\Transcodes\Controllers\TranscodeController;
use App\Admin\Users\Controllers\UserController;
use App\Admin\Videos\Controllers\VideoController;
use App\Admin\Videos\Controllers\VideoMediaController;
use App\Admin\Videos\Controllers\VideoPlaylistController;
use App\Admin\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Users
Route::resource('users', UserController::class);

// Tags
Route::resource('tags', TagController::class);

// Playlists
Route::resource('playlists', PlaylistController::class);

// Transcodes
Route::resource('transcodes', TranscodeController::class);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class);
Route::resource('videos.playlists', VideoPlaylistController::class);
Route::resource('videos.transcodes', VideoTranscodeController::class);

<?php

declare(strict_types=1);

use App\Web\Account\Controllers\HomeController;
use App\Web\Media\Controllers\MediaController;
use App\Web\Playlists\Controllers\PlaylistController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Transcodes\Controllers\TranscodeController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoMediaController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use App\Web\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Account
// Route::get('/profile', ProfileController::class)->name('profile');
// Route::get('/settings', SettingsController::class)->name('settings');

// Media
Route::resource('media', MediaController::class);

// Playlists
Route::resource('playlists', PlaylistController::class);

// // Tags
Route::resource('tags', TagController::class);

// Transcodes
Route::resource('transcodes', TranscodeController::class);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class)->scoped();
Route::resource('videos.playlists', VideoPlaylistController::class)->scoped();
Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped();

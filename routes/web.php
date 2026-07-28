<?php

declare(strict_types=1);

use App\Modules\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [VideoController::class, 'index'])->name('home');

// // Account
// Route::get('/account', AccountController::class)->name('account');
// Route::get('/security', SecurityController::class)->name('security');
// Route::get('/settings', SettingsController::class)->name('settings');
// Route::resource('notifications', NotificationsController::class)->only(['index', 'update', 'destroy']);

// // Profiles
// Route::resource('profiles', ProfileController::class)->only(['index', 'store', 'update', 'destroy']);

// // Resources
// Route::resource('collections', GroupController::class);
// Route::resource('tags', TagController::class);
// Route::resource('media', MediaController::class)->only(['update', 'destroy']);
// Route::resource('playlists', PlaylistController::class)->only(['update', 'destroy']);
// Route::resource('transcodes', TranscodeController::class)->only(['index', 'destroy']);

// // Videos
// Route::resource('videos', VideoController::class);
// Route::resource('videos.media', VideoMediaController::class)->only(['index']);
// Route::resource('videos.playlists', VideoPlaylistController::class)->scoped()->only(['index', 'store', 'update', 'destroy']);
// Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped()->only(['index', 'update', 'destroy']);

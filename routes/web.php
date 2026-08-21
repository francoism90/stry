<?php

declare(strict_types=1);

use App\Web\Account\Controllers\NotificationsController;
use App\Web\Groups\Controllers\GroupController;
use App\Web\Media\Controllers\MediaController;
use App\Web\Playlists\Controllers\PlaylistController;
use App\Web\Profiles\Controllers\ProfileController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Transcodes\Controllers\TranscodeController;
use App\Web\Users\Controllers\UserController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use App\Web\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [VideoController::class, 'index'])->name('home');

// // Account
Route::resource('notifications', NotificationsController::class)->only(['index', 'update', 'destroy']);

// // Profiles
Route::resource('profiles', ProfileController::class)->only(['index', 'store', 'update', 'destroy']);

// Tags
Route::resource('tags', TagController::class)->except(['create', 'edit']);

// Collections
Route::resource('collections', GroupController::class)->except(['create', 'edit']);

// Media
Route::resource('media', MediaController::class)->only(['update', 'destroy']);

// Playlists
Route::resource('playlists', PlaylistController::class)->only(['update', 'destroy']);

// Transcodes
Route::resource('transcodes', TranscodeController::class)->only(['index', 'destroy']);

// Users
Route::resource('users', UserController::class)->only(['index', 'update', 'destroy']);

// Videos
Route::resource('videos', VideoController::class)->except(['create', 'store', 'edit']);
Route::resource('videos.playlists', VideoPlaylistController::class)->scoped()->only(['store', 'update', 'destroy']);
Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped()->only(['update', 'destroy']);

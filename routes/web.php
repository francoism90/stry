<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Web\Account\Controllers\NotificationsController;
use Modules\Web\Groups\Controllers\GroupController;
use Modules\Web\Home\Controllers\HomeController;
use Modules\Web\Media\Controllers\MediaController;
use Modules\Web\Playlists\Controllers\PlaylistController;
use Modules\Web\Profiles\Controllers\ProfileController;
use Modules\Web\Tags\Controllers\TagController;
use Modules\Web\Transcodes\Controllers\TranscodeController;
use Modules\Web\Users\Controllers\UserController;
use Modules\Web\Videos\Controllers\VideoChapterController;
use Modules\Web\Videos\Controllers\VideoController;
use Modules\Web\Videos\Controllers\VideoPlaylistController;
use Modules\Web\Videos\Controllers\VideoTranscodeController;

// Home
Route::get('/', HomeController::class)->name('home');

// Account
Route::resource('notifications', NotificationsController::class)->only(['index', 'update', 'destroy']);

// Profiles
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
Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

// Videos
Route::resource('videos', VideoController::class)->except(['create', 'store', 'edit']);
Route::resource('videos.playlists', VideoPlaylistController::class)->scoped()->only(['store', 'update', 'destroy']);
Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped()->only(['update', 'destroy']);
Route::resource('videos.chapters', VideoChapterController::class)->scoped()->only(['store', 'update', 'destroy']);

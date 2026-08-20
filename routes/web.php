<?php

declare(strict_types=1);

use App\Web\Account\Controllers\AccountController;
use App\Web\Account\Controllers\AppearanceController;
use App\Web\Account\Controllers\NotificationsController;
use App\Web\Account\Controllers\SecurityController;
use App\Web\Account\Controllers\SettingsController;
use App\Web\Groups\Controllers\GroupController;
use App\Web\Media\Controllers\MediaController;
use App\Web\Playlists\Controllers\PlaylistController;
use App\Web\Profiles\Controllers\ProfileController;
use App\Web\Search\Controllers\SearchController;
use App\Web\Search\Controllers\SearchGroupsController;
use App\Web\Search\Controllers\SearchTagsController;
use App\Web\Search\Controllers\SearchVideosController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Transcodes\Controllers\TranscodeController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoMediaController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use App\Web\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [VideoController::class, 'index'])->name('home');

// // Account
Route::get('/account', AccountController::class)->name('account');
Route::get('/security', SecurityController::class)->name('security');
Route::get('/settings', SettingsController::class)->name('settings');
Route::get('/settings/appearance', AppearanceController::class)->name('settings.appearance');
Route::resource('notifications', NotificationsController::class)->only(['index', 'update', 'destroy']);

// // Profiles
Route::resource('profiles', ProfileController::class)->only(['index', 'store', 'update', 'destroy']);

// Tags
Route::resource('tags', TagController::class);

// Collections
Route::resource('collections', GroupController::class);

// Media
Route::resource('media', MediaController::class)->only(['update', 'destroy']);

// Playlists
Route::resource('playlists', PlaylistController::class)->only(['update', 'destroy']);

// Transcodes
Route::resource('transcodes', TranscodeController::class)->only(['index', 'destroy']);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class)->only(['index']);
Route::resource('videos.playlists', VideoPlaylistController::class)->scoped()->only(['index', 'store', 'update', 'destroy']);
Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped()->only(['index', 'update', 'destroy']);

// Search
Route::prefix('search')->name('search.')->group(function () {
    Route::get('/{query}/videos', SearchVideosController::class)->name('videos');
    Route::get('/{query}/tags', SearchTagsController::class)->name('tags');
    Route::get('/{query}/collections', SearchGroupsController::class)->name('collections');
    Route::get('/{query?}', SearchController::class)->name('index');
});

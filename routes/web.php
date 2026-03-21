<?php

declare(strict_types=1);

use App\Web\Account\Controllers\HomeController;
use App\Web\Account\Controllers\NotificationsController;
use App\Web\Account\Controllers\ProfileController;
use App\Web\Account\Controllers\SettingsController;
use App\Web\Groups\Controllers\GroupController;
use App\Web\Media\Controllers\MediaController;
use App\Web\Playlists\Controllers\PlaylistController;
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
Route::get('/', HomeController::class)->name('home');

// Account
Route::get('/profile', ProfileController::class)->name('profile');
Route::get('/settings', SettingsController::class)->name('settings');
Route::resource('notifications', NotificationsController::class)->only(['index', 'update', 'destroy']);

// Resources
Route::resource('collections', GroupController::class);
Route::resource('tags', TagController::class);
Route::resource('media', MediaController::class)->only(['update', 'destroy']);
Route::resource('playlists', PlaylistController::class)->only(['update', 'destroy']);
Route::resource('transcodes', TranscodeController::class)->only(['index', 'destroy']);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class)->scoped();
Route::resource('videos.playlists', VideoPlaylistController::class)->scoped();
Route::resource('videos.transcodes', VideoTranscodeController::class)->scoped();

// Search
Route::prefix('search')->name('search.')->group(function () {
    Route::get('/{query}/videos', SearchVideosController::class)->name('videos');
    Route::get('/{query}/tags', SearchTagsController::class)->name('tags');
    Route::get('/{query}/collections', SearchGroupsController::class)->name('collections');
    Route::get('/{query?}', SearchController::class)->name('index');
});

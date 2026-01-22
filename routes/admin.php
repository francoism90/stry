<?php

declare(strict_types=1);

use App\Admin\Dashboard\Controllers\HomeController;
use App\Admin\Tags\Controllers\TagController;
use App\Admin\Tags\Controllers\TagOrderController;
use App\Admin\Users\Controllers\UserController;
use App\Admin\Videos\Controllers\VideoController;
use App\Admin\Videos\Controllers\VideoMediaController;
use App\Admin\Videos\Controllers\VideoMediaConvertController;
use App\Admin\Videos\Controllers\VideoMediaReplaceController;
use App\Admin\Videos\Controllers\VideoPlaylistController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Users
Route::resource('users', UserController::class);

// Tags
Route::resource('tags', TagController::class);
Route::post('tags/reorder', TagOrderController::class)->name('tags.reorder');

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.media', VideoMediaController::class);
Route::post('videos/{video}/media/{media}/convert', VideoMediaConvertController::class)->name('videos.media.convert');
Route::post('videos/{video}/media/{media}/transcodes/{transcode}/replace', VideoMediaReplaceController::class)->name('videos.media.transcodes.replace');
Route::resource('videos.playlists', VideoPlaylistController::class);

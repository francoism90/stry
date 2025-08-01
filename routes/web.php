<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardController;
use App\Web\Dashboard\Controllers\SearchController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', DashboardController::class)->name('home');

// Search
Route::get('/search', SearchController::class)->name('search');

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.playlists', VideoPlaylistController::class)->shallow();

// Tags
Route::resource('tags', TagController::class);

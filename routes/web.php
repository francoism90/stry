<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardController;
use App\Web\Dashboard\Controllers\ProfileController;
use App\Web\Dashboard\Controllers\SearchController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Tags\Controllers\TagRelatedController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', DashboardController::class)->name('home');

// Profile
Route::get('/profile', ProfileController::class)->name('profile');

// Search
Route::get('/search', SearchController::class)->name('search');

// Tags
Route::resource('tags', TagController::class);
Route::resource('tags.related', TagRelatedController::class);

// Videos
Route::resource('videos', VideoController::class);
Route::resource('videos.playlists', VideoPlaylistController::class);

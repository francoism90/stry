<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardController;
use App\Web\Dashboard\Controllers\HistoryController;
use App\Web\Dashboard\Controllers\ProfileController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use App\Web\Videos\Controllers\VideoMediaController;
use App\Web\Videos\Controllers\VideoPlaylistController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/{filter?}', DashboardController::class)->name('home');

// // Profile
// Route::get('/profile', ProfileController::class)->name('profile');
// Route::get('/history', HistoryController::class)->name('history');

// // Tags
// Route::resource('tags', TagController::class);

// // Videos
// Route::resource('videos', VideoController::class);
// Route::resource('videos.media', VideoMediaController::class);
// Route::resource('videos.playlists', VideoPlaylistController::class);

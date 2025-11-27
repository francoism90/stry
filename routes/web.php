<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardController;
use App\Web\Dashboard\Controllers\ProfileController;
use App\Web\Media\Controllers\MediaController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', DashboardController::class)->name('home');

// Profile
Route::get('/profile', ProfileController::class)->name('profile');

// Media
Route::resource('media', MediaController::class)->except('create', 'store');

// Tags
Route::resource('tags', TagController::class)->except('create', 'store');

// Videos
Route::resource('videos', VideoController::class)->except('create', 'store');

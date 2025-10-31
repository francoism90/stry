<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardController;
use App\Web\Dashboard\Controllers\ExploreController;
use App\Web\Dashboard\Controllers\LibraryController;
use App\Web\Dashboard\Controllers\ProfileController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', DashboardController::class)->name('home');
Route::get('/explore', ExploreController::class)->name('explorer');
Route::get('/library', LibraryController::class)->name('library');
Route::get('/profile', ProfileController::class)->name('profile');

// Tags
Route::resource('tags', TagController::class)->except('index', 'create', 'store');

// Videos
Route::resource('videos', VideoController::class)->except('index', 'create', 'store');

<?php

declare(strict_types=1);

use App\Web\Account\Controllers\HomeController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Account
// Route::get('/profile', ProfileController::class)->name('profile');
// Route::get('/settings', SettingsController::class)->name('settings');

// // Tags
Route::apiResource('tags', TagController::class)->only(['index', 'show']);

// Videos
Route::apiResource('videos', VideoController::class)->only(['show', 'update', 'destroy']);

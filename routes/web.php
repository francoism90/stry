<?php

declare(strict_types=1);

use App\Web\Account\Controllers\HomeController;
use App\Web\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Account
// Route::get('/profile', ProfileController::class)->name('profile');
// Route::get('/settings', SettingsController::class)->name('settings');

// // Tags
// Route::get('/tags', TagController::class)->name('tags');

// Videos
Route::apiResource('/videos/{video}', VideoController::class);

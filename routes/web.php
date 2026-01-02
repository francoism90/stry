<?php

declare(strict_types=1);

use App\Client\Account\Controllers\HomeController;
use App\Client\Account\Controllers\ProfileController;
use App\Client\Account\Controllers\SettingsController;
use App\Client\Tags\Controllers\TagController;
use App\Client\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Account
Route::get('/profile', ProfileController::class)->name('profile');
Route::get('/settings', SettingsController::class)->name('settings');

// Tags
Route::get('/tags', TagController::class)->name('tags');

// Videos
Route::get('/play/{video}', VideoController::class)->name('play');

// Fallback
Route::any('/{filter?}', HomeController::class)
    ->defaults('filter', 'all')
    ->name('home');

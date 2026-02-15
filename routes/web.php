<?php

declare(strict_types=1);

use App\Client\Account\Controllers\HomeController;
use App\Client\Account\Controllers\ProfileController;
use App\Client\Account\Controllers\SettingsController;
use App\Client\Groups\Controllers\GroupClearController;
use App\Client\Groups\Controllers\GroupToggleController;
use App\Client\Tags\Controllers\TagController;
use App\Client\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Account
Route::get('/profile', ProfileController::class)->name('profile');
Route::get('/settings', SettingsController::class)->name('settings');

// Groups
Route::post('/groups/{type}/clear', GroupClearController::class)->name('groups.clear');
Route::post('/groups/{type}/{video}', GroupToggleController::class)->name('groups.toggle');

// Tags
Route::get('/tags', TagController::class)->name('tags');

// Videos
Route::get('/play/{video}', VideoController::class)->name('play');

// Fallback
Route::any('/{filter?}', HomeController::class)
    ->defaults('filter', 'all')
    ->where('filter', '^(?!admin|api).*')
    ->name('home');

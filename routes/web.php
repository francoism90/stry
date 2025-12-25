<?php

declare(strict_types=1);

use App\Client\Account\Controllers\HomeController;
use App\Client\Account\Controllers\ProfileController;
use App\Client\Tags\Controllers\TagController;
use App\Client\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Videos
Route::get('/play/{video}', VideoController::class)->name('play');

// Tags
Route::get('/tags', TagController::class)->name('tags');

// Account
Route::get('/profile', ProfileController::class)->name('profile');

// Fallback
Route::any('/{filter?}', HomeController::class)
    ->defaults('filter', 'default')
    ->name('home');

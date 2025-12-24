<?php

declare(strict_types=1);

use App\Client\Account\Controllers\ProfileController;
use App\Client\Tags\Controllers\DiscoverController;
use App\Client\Videos\Controllers\LibraryController;
use App\Client\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', LibraryController::class)->name('home');
Route::get('/play/{video}', VideoController::class)->name('play');

// Discover
Route::get('/discover', DiscoverController::class)->name('discover');

// Account
Route::get('/profile', ProfileController::class)->name('profile');

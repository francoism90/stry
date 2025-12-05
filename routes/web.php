<?php

declare(strict_types=1);

use App\Client\Account\Controllers\ProfileController;
use App\Client\Tags\Controllers\DiscoverController;
use App\Client\Tags\Controllers\TagController;
use App\Client\Videos\Controllers\FeedController;
use App\Client\Videos\Controllers\LibraryController;
use App\Client\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', FeedController::class)->name('home');

// Account
Route::get('/profile', ProfileController::class)->name('profile');

// Library
Route::get('/library', LibraryController::class)->name('library');
Route::get('/play/{video}', VideoController::class)->name('play');

// Discover
Route::get('/discover', DiscoverController::class)->name('discover');
Route::get('/tags/{tag}', TagController::class)->name('tag');

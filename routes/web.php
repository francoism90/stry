<?php

declare(strict_types=1);

use App\Client\Tags\Controllers\DiscoverController;
use App\Client\Videos\Controllers\FeedController;
use App\Client\Videos\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', FeedController::class)->name('home');

// Library
Route::get('/library', LibraryController::class)->name('library');

// Discover
Route::get('/discover', DiscoverController::class)->name('discover');

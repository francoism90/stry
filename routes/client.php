<?php

declare(strict_types=1);

use App\Client\Search\Controllers\SearchController;
use App\Client\Videos\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', LibraryController::class)->name('home');

// Search
Route::get('/search', SearchController::class)->name('search');

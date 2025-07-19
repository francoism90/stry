<?php

declare(strict_types=1);

use App\Web\Dashboard\Controllers\DashboardIndex;
use App\Web\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', DashboardIndex::class)->name('home');

// Videos
Route::resource('videos', VideoController::class)->except(['store', 'update', 'destroy']);

<?php

declare(strict_types=1);

use App\Client\Landing\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

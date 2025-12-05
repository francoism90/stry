<?php

declare(strict_types=1);

use App\Admin\Dashboard\Controllers\HomeController;
use App\Admin\Media\Controllers\MediaController;
use App\Admin\Playlists\Controllers\PlaylistController;
use App\Admin\Tags\Controllers\TagController;
use App\Admin\Users\Controllers\UserController;
use App\Admin\Videos\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', HomeController::class)->name('home');

// Users
Route::resource('users', UserController::class);

// Media
Route::resource('media', MediaController::class);

// Playlists
Route::resource('playlists', PlaylistController::class);

// Tags
Route::resource('tags', TagController::class);

// Videos
Route::resource('videos', VideoController::class);

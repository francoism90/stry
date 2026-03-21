<?php

declare(strict_types=1);

use App\Api\Groups\Controllers\GroupClearController;
use App\Api\Groups\Controllers\GroupToggleController;
use App\Api\Notifications\Controllers\MarkAllNotificationsReadController;
use App\Api\Tags\Controllers\TagOrderController;
use App\Api\Transcodes\Controllers\TranscodeImportController;
use App\Api\Users\Controllers\UserSettingsController;
use App\Api\Videos\Controllers\VideoImportController;
use App\Api\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

// Settings
Route::prefix('/settings')->name('settings.')->group(function () {
    Route::patch('/', UserSettingsController::class)->name('update');
});

// Groups
Route::prefix('/groups')->name('groups.')->group(function () {
    Route::post('/{group}/clear', GroupClearController::class)->name('clear');
    Route::post('/{group}/{video}', GroupToggleController::class)->name('toggle');
});

// Notifications
Route::prefix('/notifications')->name('notifications.')->group(function () {
    Route::post('/mark-all-read', MarkAllNotificationsReadController::class)->name('mark-all-read');
});

// Tags
Route::prefix('/tags')->name('tags.')->group(function () {
    Route::post('/order', TagOrderController::class)->name('reorder');
});

// Transcodes
Route::prefix('/transcodes')->name('transcodes.')->group(function () {
    Route::post('/{transcode}/import', TranscodeImportController::class)->name('import');
});

// Videos
Route::prefix('/videos')->name('videos.')->group(function () {
    Route::post('/import', VideoImportController::class)->name('import');
    Route::post('/{video}/transcode', VideoTranscodeController::class)->name('transcode');
});

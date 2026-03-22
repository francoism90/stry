<?php

declare(strict_types=1);

use App\Api\Groups\Controllers\GroupClearController;
use App\Api\Groups\Controllers\GroupToggleController;
use App\Api\Notifications\Controllers\MarkAllNotificationsReadController;
use App\Api\Tags\Controllers\TagOrderController;
use App\Api\Users\Controllers\UserSettingsController;
use App\Api\Videos\Controllers\VideoImportController;
use App\Api\Videos\Controllers\VideoLikeController;
use App\Api\Videos\Controllers\VideoSaveController;
use App\Api\Videos\Controllers\VideoTranscodeController;
use App\Api\Videos\Controllers\VideoTranscodedController;
use Illuminate\Support\Facades\Route;

// Settings
Route::prefix('/settings')->name('settings.')->group(function () {
    Route::patch('/', UserSettingsController::class)->name('update');
});

// Groups
Route::prefix('/groups')->name('groups.')->group(function () {
    Route::post('/{group}/clear', GroupClearController::class)->name('clear');
    Route::post('/{group}/videos/{video}/toggle', GroupToggleController::class)->name('videos.toggle');
});

// Notifications
Route::prefix('/notifications')->name('notifications.')->group(function () {
    Route::post('/mark-all-read', MarkAllNotificationsReadController::class)->name('mark-all-read');
});

// Tags
Route::prefix('/tags')->name('tags.')->group(function () {
    Route::post('/reorder', TagOrderController::class)->name('reorder');
});

// Videos
Route::prefix('/videos')->name('videos.')->group(function () {
    Route::post('/import', VideoImportController::class)->name('import');
    Route::post('/{video}/like', VideoLikeController::class)->name('like');
    Route::post('/{video}/save', VideoSaveController::class)->name('save');
    Route::post('/{video}/transcode', VideoTranscodeController::class)->name('transcode');
    Route::post('/{video}/transcoded', VideoTranscodedController::class)->name('transcoded');
});

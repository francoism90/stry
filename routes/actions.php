<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupClearController;
use App\Web\Groups\Controllers\GroupToggleController;
use App\Web\Media\Controllers\MediaDownloadController;
use App\Web\Notifications\Controllers\MarkAllNotificationsReadController;
use App\Web\Profiles\Controllers\SwitchProfileController;
use App\Web\Settings\Controllers\ApplicationSettingsController;
use App\Web\Shuffle\Controllers\ShuffleController;
use App\Web\Tags\Controllers\TagOrderController;
use App\Web\Users\Controllers\UserSettingsController;
use App\Web\Videos\Controllers\VideoDispatchTranscodeController;
use App\Web\Videos\Controllers\VideoImportController;
use App\Web\Videos\Controllers\VideoLikeController;
use App\Web\Videos\Controllers\VideoSaveController;
use App\Web\Videos\Controllers\VideoTranscodedController;
use Illuminate\Support\Facades\Route;

// Settings
Route::prefix('/settings')->name('settings.')->group(function () {
    Route::patch('/', UserSettingsController::class)->name('update');
    Route::patch('/application', ApplicationSettingsController::class)->name('application.update');
});

// Profiles
Route::prefix('/profiles')->name('profiles.')->group(function () {
    Route::post('/{profile}/switch', SwitchProfileController::class)->name('switch');
});

// Groups
Route::prefix('/groups')->name('groups.')->group(function () {
    Route::post('/{group}/clear', GroupClearController::class)->name('clear');
    Route::post('/{group}/videos/{video}/toggle', GroupToggleController::class)->name('videos.toggle');
});

// Media
Route::prefix('/media')->name('media.')->group(function () {
    Route::get('/{media}/download', MediaDownloadController::class)->name('download');
});

// Notifications
Route::prefix('/notifications')->name('notifications.')->group(function () {
    Route::post('/mark-all-read', MarkAllNotificationsReadController::class)->name('mark-all-read');
});

// Shuffle
Route::get('/shuffle/{type}', ShuffleController::class)->name('shuffle');

// Tags
Route::prefix('/tags')->name('tags.')->group(function () {
    Route::post('/reorder', TagOrderController::class)->name('reorder');
});

// Videos
Route::prefix('/videos')->name('videos.')->group(function () {
    Route::post('/import', VideoImportController::class)->name('import');
    Route::post('/{video}/like', VideoLikeController::class)->name('like');
    Route::post('/{video}/save', VideoSaveController::class)->name('save');
    Route::post('/{video}/transcode', VideoDispatchTranscodeController::class)->name('transcode');
    Route::post('/{video}/transcoded', VideoTranscodedController::class)->name('transcoded');
});

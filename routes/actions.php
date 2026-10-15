<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Web\Groups\Controllers\GroupClearController;
use Modules\Web\Groups\Controllers\GroupToggleController;
use Modules\Web\Notifications\Controllers\MarkAllNotificationsReadController;
use Modules\Web\Profiles\Controllers\SwitchProfileController;
use Modules\Web\Settings\Controllers\ApplicationSettingsController;
use Modules\Web\Settings\Controllers\ChapterSettingsController;
use Modules\Web\Settings\Controllers\PlaybackSettingsController;
use Modules\Web\Settings\Controllers\ProcessingSettingsController;
use Modules\Web\Settings\Controllers\ReelSettingsController;
use Modules\Web\Settings\Controllers\StorageSettingsController;
use Modules\Web\Shuffle\Controllers\ShuffleController;
use Modules\Web\Users\Controllers\UserSettingsController;
use Modules\Web\Videos\Controllers\VideoDispatchTranscodeController;
use Modules\Web\Videos\Controllers\VideoExportController;
use Modules\Web\Videos\Controllers\VideoImportController;
use Modules\Web\Videos\Controllers\VideoLikeController;
use Modules\Web\Videos\Controllers\VideoMoveController;
use Modules\Web\Videos\Controllers\VideoSaveController;
use Modules\Web\Videos\Controllers\VideoTranscodedController;

// Settings
Route::prefix('/settings')->name('settings.')->group(function () {
    Route::patch('/', UserSettingsController::class)->name('update');
    Route::singleton('application', ApplicationSettingsController::class)->only(['show', 'update']);
    Route::singleton('playback', PlaybackSettingsController::class)->only(['show', 'update']);
    Route::singleton('chapters', ChapterSettingsController::class)->only(['show', 'update']);
    Route::singleton('processing', ProcessingSettingsController::class)->only(['show', 'update']);
    Route::singleton('reels', ReelSettingsController::class)->only(['show', 'update']);
    Route::singleton('storage', StorageSettingsController::class)->only(['show', 'update']);
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

// Notifications
Route::prefix('/notifications')->name('notifications.')->group(function () {
    Route::post('/mark-all-read', MarkAllNotificationsReadController::class)->name('mark-all-read');
});

// Shuffle
Route::get('/shuffle/{type}', ShuffleController::class)->name('shuffle');

// Videos
Route::prefix('/videos')->name('videos.')->group(function () {
    Route::post('/import', VideoImportController::class)->name('import');
    Route::post('/{video}/export', VideoExportController::class)->name('export');
    Route::post('/{video}/like', VideoLikeController::class)->name('like');
    Route::post('/{video}/move', VideoMoveController::class)->name('move');
    Route::post('/{video}/save', VideoSaveController::class)->name('save');
    Route::post('/{video}/transcode', VideoDispatchTranscodeController::class)->name('transcode');
    Route::post('/{video}/transcoded', VideoTranscodedController::class)->name('transcoded');
});

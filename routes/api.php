<?php

declare(strict_types=1);

use App\Api\Authentication\Controllers\HomeController;
use App\Api\Groups\Controllers\GroupClearController;
use App\Api\Groups\Controllers\GroupToggleController;
use App\Api\Notifications\Controllers\MarkAllNotificationsReadController;
use App\Api\Playlists\Controllers\PlaylistManifestController;
use App\Api\Playlists\Controllers\PlaylistSessionController;
use App\Api\Tags\Controllers\TagController;
use App\Api\Tags\Controllers\TagOrderController;
use App\Api\Transcodes\Controllers\TranscodeImportController;
use App\Api\Users\Controllers\UserSettingsController;
use App\Api\Videos\Controllers\VideoImportController;
use App\Api\Videos\Controllers\VideoTranscodeController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->prefix('v1')->group(function () {
    // Authentication
    Route::get('/', HomeController::class)->name('home');

    // Groups
    Route::post('/groups/{group}/clear', GroupClearController::class)->name('groups.clear');
    Route::post('/groups/{group}/{video}', GroupToggleController::class)->name('groups.toggle');

    // Tags
    Route::apiResource('tags', TagController::class)->only('index');
    Route::post('/tags/order', TagOrderController::class)->name('tags.reorder');

    // Transcodes
    Route::post('/transcodes/{transcode}/import', TranscodeImportController::class)->name('transcodes.import');

    // Videos
    Route::post('/videos/import', VideoImportController::class)->name('videos.import');
    Route::post('/videos/{video}/transcode', VideoTranscodeController::class)->name('videos.transcode');

    // Notifications
    Route::post('/notifications/mark-all-read', MarkAllNotificationsReadController::class)->name('notifications.mark-all-read');

    // Settings
    Route::patch('/settings', UserSettingsController::class)->name('settings.update');

    // VOD - Playlists
    Route::get('/play/{playlist}/{path}', PlaylistManifestController::class)
        ->where('path', '.*')
        ->name('play.manifest');

    // VOD - Analytics
    Route::post('/record/{playlist}', PlaylistSessionController::class)->name('play.session');
});

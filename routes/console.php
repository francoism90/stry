<?php

declare(strict_types=1);

use Domain\Playlists\Models\Playlist;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\PruneStaleTagsCommand;
use Illuminate\Database\Console\PruneCommand as PruneModels;
use Illuminate\Support\Facades\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use Laravel\Sanctum\Console\Commands\PruneExpired;
use Laravel\Telescope\Console\PruneCommand;

Schedule::command(PruneStaleTagsCommand::class)
    ->hourly()
    ->runInBackground();

Schedule::command(ClearResetsCommand::class)
    ->everyFifteenMinutes()
    ->runInBackground();

Schedule::command(SnapshotCommand::class)
    ->everyFiveMinutes()
    ->runInBackground();

Schedule::command(PruneExpired::class, ['--hours=24'])
    ->dailyAt('01:30')
    ->runInBackground();

Schedule::command(PruneCommand::class)
    ->dailyAt('02:00')
    ->runInBackground();

Schedule::command(PruneModels::class, [
    '--model' => [
        Playlist::class,
    ],
])
    ->everyFifteenMinutes()
    ->runInBackground();

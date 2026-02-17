<?php

declare(strict_types=1);

use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\PruneStaleTagsCommand;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Support\Facades\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use Laravel\Sanctum\Console\Commands\PruneExpired;

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
    ->withoutOverlapping()
    ->dailyAt('01:30')
    ->runInBackground();

Schedule::command(PruneCommand::class, [
    '--model' => [
        Domain\Groups\Models\Group::class,
        Domain\Playlists\Models\Playlist::class,
        Domain\Transcodes\Models\Transcode::class,
    ]])
    ->withoutOverlapping()
    ->dailyAt('02:30')
    ->runInBackground();

<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Transcodes\Models\Transcode;
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
        Group::class,
        Transcode::class,
    ]])
    ->withoutOverlapping()
    ->dailyAt('02:30')
    ->runInBackground();

Schedule::command('media:prune', ['--older-than' => 10080])
    ->withoutOverlapping()
    ->dailyAt('03:30')
    ->runInBackground();

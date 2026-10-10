<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Transcodes\Models\Transcode;
use Foxws\Media\Commands\CleanCommand as CleanMediaCommand;
use Foxws\Media\Commands\PruneCommand as PruneMediaCommand;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\PruneStaleTagsCommand;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Support\Facades\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use Laravel\Sanctum\Console\Commands\PruneExpired;

/**
 * In production, the scheduler is a oneshot container that runs `schedule:run` once a minute and
 * stops when it exits, killing anything still running. Don't use runInBackground() here.
 */
Schedule::command(PruneStaleTagsCommand::class)
    ->hourly();

Schedule::command(ClearResetsCommand::class)
    ->everyFifteenMinutes();

Schedule::command(SnapshotCommand::class)
    ->everyFiveMinutes();

Schedule::command(CleanMediaCommand::class)
    ->withoutOverlapping()
    ->hourly();

Schedule::command(PruneExpired::class, ['--hours=24'])
    ->withoutOverlapping()
    ->dailyAt('01:30');

Schedule::command(PruneCommand::class, [
    '--model' => [
        Group::class,
        Transcode::class,
    ]])
    ->withoutOverlapping()
    ->dailyAt('02:30');

Schedule::command(PruneMediaCommand::class, ['--older-than' => 10080])
    ->withoutOverlapping()
    ->dailyAt('03:30');

<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Shared\Enums\SnapshotFrequency;
use Domain\Transcodes\Models\Transcode;
use Foxws\Media\Commands\CleanCommand as CleanMediaCommand;
use Foxws\Media\Commands\PruneCommand as PruneMediaCommand;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\PruneStaleTagsCommand;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Support\Facades\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use Laravel\Sanctum\Console\Commands\PruneExpired;
use Support\System\Commands\CreateSnapshotCommand;

/**
 * In production, the scheduler is a oneshot container that runs `schedule:run` and stops when it
 * exits, killing anything still running. Don't use runInBackground() here.
 *
 * Every run wakes the database and cache, so keep frequent tasks to a minimum and
 * group the rest at a few fixed times at night. Tasks that share a minute run one after another in
 * the same run, so only group quick ones: the scheduler container is stopped after 5 minutes.
 */
Schedule::command(SnapshotCommand::class)
    ->hourly();

Schedule::command(PruneStaleTagsCommand::class)
    ->dailyAt('01:30');

Schedule::command(ClearResetsCommand::class)
    ->dailyAt('01:30');

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

Schedule::command(CleanMediaCommand::class)
    ->withoutOverlapping()
    ->dailyAt('03:30');

Schedule::command(PruneMediaCommand::class, ['--older-than' => 10080])
    ->withoutOverlapping()
    ->dailyAt('03:30');

if ($snapshotExpression = SnapshotFrequency::configured()->expression()) {
    Schedule::command(CreateSnapshotCommand::class)
        ->withoutOverlapping()
        ->cron($snapshotExpression);
}

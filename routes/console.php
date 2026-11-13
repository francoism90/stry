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
 * exits, killing anything still running. Don't use runInBackground() here. Give withoutOverlapping()
 * a short expiry, so a run killed by the container's timeout doesn't keep its lock for a day.
 *
 * Every run wakes the database and cache, so all tasks share the hourly run at minute 0 instead
 * of adding wake-ups of their own. Tasks in the same run go one after another and the container
 * is stopped after 5 minutes, so each hour gets one kind of work: quick cleanup at 01:00, media
 * at 02:00 and the database snapshot alone at 03:00.
 *
 * The hourly Horizon snapshot only runs in production, so the database and cache of other
 * environments can scale to zero between the nightly tasks.
 */
Schedule::command(SnapshotCommand::class)
    ->environments('production')
    ->hourly();

Schedule::command(PruneStaleTagsCommand::class)
    ->dailyAt('01:00');

Schedule::command(ClearResetsCommand::class)
    ->dailyAt('01:00');

Schedule::command(PruneExpired::class, ['--hours=24'])
    ->withoutOverlapping(10)
    ->dailyAt('01:00');

Schedule::command(PruneCommand::class, [
    '--model' => [
        Group::class,
        Transcode::class,
    ]])
    ->withoutOverlapping(10)
    ->dailyAt('01:00');

Schedule::command(CleanMediaCommand::class)
    ->dailyAt('02:00');

Schedule::command(PruneMediaCommand::class, ['--older-than' => 10080])
    ->withoutOverlapping(10)
    ->dailyAt('02:00');

if ($snapshotExpression = SnapshotFrequency::configured()->expression()) {
    Schedule::command(CreateSnapshotCommand::class)
        ->cron($snapshotExpression);
}

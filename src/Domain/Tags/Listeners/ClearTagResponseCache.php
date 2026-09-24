<?php

declare(strict_types=1);

namespace Domain\Tags\Listeners;

use Domain\Tags\Models\Tag;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessed;
use Laravel\Scout\Scout;

/**
 * Tag model events clear the response cache immediately, but Scout updates
 * the search index on the queue. A request arriving in between re-caches the
 * stale search results, so clear again once the index job has finished.
 */
class ClearTagResponseCache
{
    public function handle(JobProcessed $event): void
    {
        if (! $this->isTagSearchIndexJob($event->job)) {
            return;
        }

        Tag::clearResponseCache();
    }

    protected function isTagSearchIndexJob(Job $job): bool
    {
        $searchIndexJobs = [Scout::$makeSearchableJob, Scout::$removeFromSearchJob];

        if (! in_array($job->resolveName(), $searchIndexJobs, true)) {
            return false;
        }

        // Match the serialized model class without restoring the models from the database
        $command = (string) data_get($job->payload(), 'data.command', '');

        return str_contains($command, serialize(Tag::class));
    }
}

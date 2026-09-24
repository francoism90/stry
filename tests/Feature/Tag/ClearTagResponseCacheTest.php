<?php

declare(strict_types=1);

use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\CallQueuedHandler;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Jobs\SyncJob;
use Laravel\Scout\Jobs\MakeSearchable;
use Laravel\Scout\Jobs\RemoveFromSearch;
use Spatie\ResponseCache\Facades\ResponseCache;

function processedQueueJob(ShouldQueue $command): JobProcessed
{
    $payload = json_encode([
        'displayName' => $command::class,
        'job' => CallQueuedHandler::class.'@call',
        'data' => [
            'commandName' => $command::class,
            'command' => serialize($command),
        ],
    ]);

    return new JobProcessed('sync', new SyncJob(app(), $payload, 'sync', 'default'));
}

it('clears the tag response cache after tags are indexed', function (string $jobClass) {
    $tags = Tag::factory()->count(2)->create();

    ResponseCache::spy();

    event(processedQueueJob(new $jobClass($tags)));

    ResponseCache::shouldHaveReceived('clear')->with(Tag::responseCacheTags());
})->with([
    'make searchable' => MakeSearchable::class,
    'remove from search' => RemoveFromSearch::class,
]);

it('does not clear the tag response cache after other models are indexed', function () {
    $videos = Video::factory()->count(2)->create();

    ResponseCache::shouldReceive('clear')->never();

    event(processedQueueJob(new MakeSearchable($videos)));
});

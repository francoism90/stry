<?php

declare(strict_types=1);

use Domain\Videos\Jobs\GenerateVideoReel;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Bus;

function addRegeneratedReelMedia(Video $video, string $collection): Video
{
    $video->media()->create([
        'collection_name' => $collection,
        'name' => $collection,
        'file_name' => "{$collection}.mp4",
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    return $video;
}

it('queues the reels of every video with a clip', function () {
    $first = addRegeneratedReelMedia(Video::factory()->create(), 'clips');
    $second = addRegeneratedReelMedia(addRegeneratedReelMedia(Video::factory()->create(), 'clips'), 'reels');
    Video::factory()->create();

    $this->artisan('videos:reels', ['--force' => true])->assertSuccessful();

    Bus::assertDispatchedTimes(GenerateVideoReel::class, 2);
    Bus::assertDispatched(GenerateVideoReel::class, fn (GenerateVideoReel $job): bool => $job->video->is($first));
    Bus::assertDispatched(GenerateVideoReel::class, fn (GenerateVideoReel $job): bool => $job->video->is($second));
});

it('queues only the given videos or those without a reel', function () {
    $without = addRegeneratedReelMedia(Video::factory()->create(), 'clips');
    $with = addRegeneratedReelMedia(addRegeneratedReelMedia(Video::factory()->create(), 'clips'), 'reels');

    $this->artisan('videos:reels', ['--missing' => true, '--force' => true])->assertSuccessful();

    Bus::assertDispatchedTimes(GenerateVideoReel::class, 1);
    Bus::assertDispatched(GenerateVideoReel::class, fn (GenerateVideoReel $job): bool => $job->video->is($without));

    $this->artisan('videos:reels', ['video' => [$with->getKey()], '--force' => true])->assertSuccessful();

    Bus::assertDispatched(GenerateVideoReel::class, fn (GenerateVideoReel $job): bool => $job->video->is($with));
});

it('asks before queueing', function () {
    addRegeneratedReelMedia(Video::factory()->create(), 'clips');

    $this->artisan('videos:reels')
        ->expectsConfirmation('Regenerate the reels of 1 videos?', 'no')
        ->assertSuccessful();

    Bus::assertNotDispatched(GenerateVideoReel::class);
});

<?php

declare(strict_types=1);

use Domain\Videos\Jobs\WarmVideoStream;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Bus;

function createWarmedClip(Video $video): Video
{
    $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
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

it('queues the streams of every video with a clip', function () {
    $first = createWarmedClip(Video::factory()->create());
    $second = createWarmedClip(Video::factory()->create());
    Video::factory()->create();

    $this->artisan('videos:warm', ['--force' => true])->assertSuccessful();

    Bus::assertDispatchedTimes(WarmVideoStream::class, 2);
    Bus::assertDispatched(WarmVideoStream::class, fn (WarmVideoStream $job): bool => $job->video->is($first));
    Bus::assertDispatched(WarmVideoStream::class, fn (WarmVideoStream $job): bool => $job->video->is($second));
});

it('queues only the given videos', function () {
    createWarmedClip(Video::factory()->create());
    $given = createWarmedClip(Video::factory()->create());

    $this->artisan('videos:warm', ['video' => [$given->getKey()], '--force' => true])->assertSuccessful();

    Bus::assertDispatchedTimes(WarmVideoStream::class, 1);
    Bus::assertDispatched(WarmVideoStream::class, fn (WarmVideoStream $job): bool => $job->video->is($given));
});

it('asks before queueing', function () {
    createWarmedClip(Video::factory()->create());

    $this->artisan('videos:warm')
        ->expectsConfirmation('Warm the streams of 1 videos?', 'no')
        ->assertSuccessful();

    Bus::assertNotDispatched(WarmVideoStream::class);
});

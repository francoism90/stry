<?php

declare(strict_types=1);

use Domain\Videos\Jobs\RenderVideoRenditions;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Bus;

it('queues the renditions of videos with a clip', function () {
    Bus::fake();
    $video = Video::factory()->create();
    Video::factory()->create();
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

    $this->artisan('videos:renditions', ['--force' => true])->assertSuccessful();

    Bus::assertDispatchedTimes(RenderVideoRenditions::class, 1);
    Bus::assertDispatched(RenderVideoRenditions::class, fn (RenderVideoRenditions $job) => $job->video->is($video));
});

it('queues nothing without videos to render', function () {
    Bus::fake();

    $this->artisan('videos:renditions', ['--force' => true])->assertSuccessful();

    Bus::assertNothingDispatched();
});

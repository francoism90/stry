<?php

declare(strict_types=1);

use Domain\Videos\Jobs\RenderVideoRenditions;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\QueueVideoRenditions;
use Domain\Videos\Settings\PlaybackSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function createQueueRenditionsClip(Video $video, array $heights = []): void
{
    $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => $heights !== [] ? ['renditions' => ['heights' => $heights, 'paths' => []]] : [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('queues the renditions when the clip has other ones than the settings ask for', function () {
    Bus::fake();
    PlaybackSettings::fake(['renditions' => [720, 480]]);
    $video = Video::factory()->create();
    createQueueRenditionsClip($video, [720]);

    app(QueueVideoRenditions::class)->handle($video, fn (Video $video) => $video);

    Bus::assertDispatched(RenderVideoRenditions::class, fn (RenderVideoRenditions $job) => $job->video->is($video));
});

it('leaves clips alone that have the renditions the settings ask for', function (array $settings, array $clip) {
    Bus::fake();
    PlaybackSettings::fake(['renditions' => $settings]);
    $video = Video::factory()->create();
    createQueueRenditionsClip($video, $clip);

    app(QueueVideoRenditions::class)->handle($video, fn (Video $video) => $video);

    Bus::assertNotDispatched(RenderVideoRenditions::class);
})->with([
    'none asked' => [[], []],
    'same ones' => [[720, 480], [720, 480]],
]);

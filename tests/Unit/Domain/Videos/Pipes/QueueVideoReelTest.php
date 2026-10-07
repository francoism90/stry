<?php

declare(strict_types=1);

use Domain\Videos\Jobs\GenerateVideoReel;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\QueueVideoReel;
use Domain\Videos\Settings\ReelSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function addReelPipeMedia(Video $video, string $collection): void
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
}

it('queues a reel for a video with a clip', function () {
    ReelSettings::fake(['enabled' => true]);
    $video = Video::factory()->create();
    addReelPipeMedia($video, 'clips');

    app(QueueVideoReel::class)->handle($video, fn (Video $video) => $video);

    Bus::assertDispatched(GenerateVideoReel::class, fn (GenerateVideoReel $job): bool => $job->video->is($video));
});

it('skips videos with a reel, without clips, or when reels are off', function (bool $hasReel, bool $hasClip, bool $enabled) {
    ReelSettings::fake(['enabled' => $enabled]);
    $video = Video::factory()->create();

    if ($hasClip) {
        addReelPipeMedia($video, 'clips');
    }

    if ($hasReel) {
        addReelPipeMedia($video, 'reels');
    }

    app(QueueVideoReel::class)->handle($video, fn (Video $video) => $video);

    Bus::assertNotDispatched(GenerateVideoReel::class);
})->with([
    'already has a reel' => [true, true, true],
    'no clips' => [false, false, true],
    'reels off' => [false, true, false],
]);

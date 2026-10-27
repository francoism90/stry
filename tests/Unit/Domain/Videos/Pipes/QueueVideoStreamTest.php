<?php

declare(strict_types=1);

use Domain\Videos\Jobs\WarmVideoStream;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\QueueVideoStream;
use Domain\Videos\Settings\ProcessingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('queues warming the stream of a video with a clip', function () {
    $video = Video::factory()->create();
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

    app(QueueVideoStream::class)->handle($video, fn (Video $video) => $video);

    Bus::assertDispatched(WarmVideoStream::class, fn (WarmVideoStream $job): bool => $job->video->is($video));
});

it('skips videos without a clip', function () {
    $video = Video::factory()->create();

    app(QueueVideoStream::class)->handle($video, fn (Video $video) => $video);

    Bus::assertNotDispatched(WarmVideoStream::class);
});

it('skips warming when it is disabled', function () {
    $video = Video::factory()->create();
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

    app(ProcessingSettings::class)->fill(['warm_streams' => false])->save();

    app(QueueVideoStream::class)->handle($video, fn (Video $video) => $video);

    Bus::assertNotDispatched(WarmVideoStream::class);
});

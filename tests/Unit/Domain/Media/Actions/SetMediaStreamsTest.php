<?php

declare(strict_types=1);

use Domain\Media\Actions\SetMediaStreams;
use Domain\Videos\Models\Video;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createStreamsMedia(string $mimeType = 'video/mp4'): Domain\Media\Models\Media
{
    return Video::factory()->create()->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => $mimeType,
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('stores the probed streams with their relevant keys', function () {
    Storage::fake('media');
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 120, width: 1280, height: 720)]);

    $media = createStreamsMedia();

    app(SetMediaStreams::class)->handle($media);

    $streams = $media->refresh()->getCustomProperty('streams');

    expect($streams)->toHaveCount(2)
        ->and($streams[0])->toMatchArray(['codec_type' => 'video', 'width' => 1280, 'height' => 720])
        ->and($streams[0])->not->toHaveKey('tags')
        ->and($streams[1])->toMatchArray(['codec_type' => 'audio']);
});

it('fills missing stream values from the container format', function () {
    Storage::fake('media');
    $probe = FakeProbe::video(duration: 120);
    unset($probe['streams'][0]['duration']);
    Media::fake(['clip.mp4' => $probe]);

    $media = createStreamsMedia();

    app(SetMediaStreams::class)->handle($media);

    expect((float) $media->refresh()->getCustomProperty('streams')[0]['duration'])->toBe(120.0);
});

it('skips media that is not audio or video', function () {
    Storage::fake('media');
    Media::fake();

    $media = createStreamsMedia('text/vtt');

    app(SetMediaStreams::class)->handle($media);

    expect($media->refresh()->hasCustomProperty('streams'))->toBeFalse();
    Media::assertNothingRan();
});

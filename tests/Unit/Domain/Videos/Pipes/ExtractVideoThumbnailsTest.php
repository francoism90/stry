<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\ExtractVideoThumbnails;
use Domain\Videos\Settings\ProcessingSettings;
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('conversions');
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 120)]);
});

function createThumbnailsClip(Video $video): void
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
}

it('samples the best clip from its keyframes into sprite sheets on the thumbnails disk', function () {
    $video = Video::factory()->create();
    createThumbnailsClip($video);

    app(ExtractVideoThumbnails::class)->handle($video, fn (Video $video) => $video);

    $thumbnails = $video->fresh()->getThumbnails();

    expect($thumbnails)->not->toBeNull()
        ->and($thumbnails->disk->name())->toBe('conversions')
        ->and($thumbnails->sprites)->toBe(["{$video->getKey()}/thumbnails_001.jpg"])
        ->and($thumbnails->interval)->toBe(5.0);
    Storage::disk('conversions')->assertExists($thumbnails->paths());
    Media::assertRan(Executable::FFMpeg, fn (array $arguments) => in_array('-skip_frame', $arguments, true));
});

it('skips videos that already have thumbnails, have no clips, or when extraction is off', function (bool $hasThumbnails, bool $hasClip, bool $enabled) {
    ProcessingSettings::fake(['extract_storyboard' => $enabled]);
    $video = Video::factory()->create();

    if ($hasThumbnails) {
        $video->forceFill(['thumbnails' => ['disk' => 'conversions', 'sprites' => ['a.jpg']]])->saveQuietly();
    }

    if ($hasClip) {
        createThumbnailsClip($video);
    }

    app(ExtractVideoThumbnails::class)->handle($video, fn (Video $video) => $video);

    Media::assertRanTimes(Executable::FFMpeg, 0);
})->with([
    'already sampled' => [true, true, true],
    'no clips' => [false, false, true],
    'extraction off' => [false, true, false],
]);

it('deletes the thumbnail files when the video is force deleted', function () {
    $video = Video::factory()->create();
    createThumbnailsClip($video);
    app(ExtractVideoThumbnails::class)->handle($video, fn (Video $video) => $video);
    $paths = $video->fresh()->getThumbnails()->paths();

    $video->fresh()->forceDelete();

    Storage::disk('conversions')->assertMissing($paths);
});

<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateVideoReel;
use Domain\Videos\Models\Video;
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('conversions');
});

function createReelClip(Video $video): Domain\Media\Models\Media
{
    return $video->media()->create([
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

it('joins the cuts into a vertical reel kept on the video', function () {
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)])->scenes('clip.mp4', [20.0, 60.0, 100.0, 140.0]);
    $video = Video::factory()->create();
    createReelClip($video);

    $reel = app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => str_contains(
        implode(' ', $arguments),
        'scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920',
    ));

    expect($reel)->not->toBeNull()
        ->and($video->fresh()->getReel()?->is($reel))->toBeTrue()
        ->and($reel->getCustomProperty('clips'))->toBe([[20.5, 24.5], [60.5, 64.5], [100.5, 104.5], [140.5, 144.5]]);
    Storage::disk('conversions')->assertExists($reel->getPathRelativeToRoot());
});

it('replaces the previous reel', function () {
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    $first = app(CreateVideoReel::class)->handle($video);
    $second = app(CreateVideoReel::class)->handle($video->fresh());

    expect($video->fresh()->getMedia('reels'))->toHaveCount(1)
        ->and($video->fresh()->getReel()?->is($second))->toBeTrue();
    Storage::disk('conversions')->assertMissing($first->getPathRelativeToRoot());
});

it('makes no reel of videos without clips or too short to cut', function (bool $hasClip, float $duration) {
    Media::fake(['clip.mp4' => FakeProbe::video(duration: $duration)]);
    $video = Video::factory()->create();

    if ($hasClip) {
        createReelClip($video);
    }

    expect(app(CreateVideoReel::class)->handle($video))->toBeNull()
        ->and($video->fresh()->hasReel())->toBeFalse();
    Media::assertRanTimes(Executable::FFMpeg, 0);
})->with([
    'no clips' => [false, 200],
    'too short' => [true, 30],
]);

<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateVideoReel;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ReelSettings;
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
    ReelSettings::fake(['zoom' => 100]);
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

it('uses the reel size and frame rate from the reel settings', function () {
    ReelSettings::fake(['width' => 720, 'height' => 1280, 'fps' => 24, 'zoom' => 100]);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => str_contains(
        implode(' ', $arguments),
        'scale=720:1280:force_original_aspect_ratio=increase,crop=720:1280,setsar=1,fps=24',
    ));
});

it('zooms a cropped video into the frame on a blurred copy of itself, or letterboxes it', function (array $settings, int $width, int $height, string $filter) {
    ReelSettings::fake($settings);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200, width: $width, height: $height)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => str_contains(implode(' ', $arguments), $filter));
})->with([
    'landscape at the default zoom' => [[], 1920, 1080, '[joined]split[background][foreground];[background]scale=270:480:force_original_aspect_ratio=increase,crop=270:480,setsar=1,boxblur=10:1,lutyuv=y=val*0.5,scale=1080:1920[blurred];[foreground]scale=1080:1264:force_original_aspect_ratio=increase,crop=1080:1264,setsar=1[fitted];[blurred][fitted]overlay=(W-w)/2:(H-h)/2,setsar=1,fps=30'],
    'landscape, whole' => [['zoom' => 0], 1920, 1080, '[foreground]scale=1080:608:force_original_aspect_ratio=increase,crop=1080:608,setsar=1[fitted]'],
    'landscape, filling the frame' => [['zoom' => 100], 1920, 1080, '[joined]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,setsar=1,fps=30'],
    'portrait fills the frame' => [[], 1080, 1920, '[joined]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,setsar=1,fps=30'],
    'letterbox' => [['fit' => 'letterbox'], 1920, 1080, '[joined]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2:color=black,setsar=1,fps=30'],
]);

it('encodes at the reel quality, as a crf on the cpu or a qp on the gpu', function (?string $hardware, string $option) {
    config(['media.ladder.hardware' => $hardware ?? 'none']);
    ReelSettings::fake(['quality' => 30, 'hardware' => $hardware !== null]);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => str_contains(implode(' ', $arguments), "{$option} 30"));
})->with([
    'cpu' => [null, '-crf'],
    'vaapi' => ['vaapi', '-qp'],
]);

it('encodes with the reel codec', function (string $codec, string $encoder) {
    config(['media.ladder.hardware' => 'none']);
    ReelSettings::fake(['codec' => $codec]);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => str_contains(implode(' ', $arguments), "-c:v {$encoder}"));
})->with([
    'h264' => ['h264', 'libx264'],
    'hevc' => ['hevc', 'libx265'],
    'av1' => ['av1', 'libsvtav1'],
]);

it('encodes the reel on the configured gpu', function () {
    config(['media.ladder.hardware' => 'vaapi']);
    ReelSettings::fake(['hardware' => true]);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => in_array('-vaapi_device', $arguments, true)
        && str_contains(implode(' ', $arguments), 'fps=30,format=nv12,hwupload[v]')
        && in_array('h264_vaapi', $arguments, true));
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

it('makes no reel of videos without clips or without a duration', function (bool $hasClip, float $duration) {
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
    'no duration' => [true, 0],
]);

it('encodes on the cpu unless the reel settings turn the gpu on', function () {
    config(['media.ladder.hardware' => 'vaapi']);
    ReelSettings::fake(['codec' => 'hevc']);
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 200)]);
    $video = Video::factory()->create();
    createReelClip($video);

    app(CreateVideoReel::class)->handle($video);

    Media::assertRan(Executable::FFMpeg, fn (array $arguments): bool => in_array('libx265', $arguments, true)
        && ! in_array('-vaapi_device', $arguments, true));
});

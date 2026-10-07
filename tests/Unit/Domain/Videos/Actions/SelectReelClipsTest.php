<?php

declare(strict_types=1);

use Domain\Chapters\Enums\ChapterType;
use Domain\Videos\Actions\SelectReelClips;
use Domain\Videos\Models\Video;
use Foxws\Media\Facades\Media;
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return list<array{float, float}>
 */
function selectReelCuts(Video $video, float $duration, array $changes): array
{
    $fake = Media::fake(['clip.mp4' => FakeProbe::video(duration: $duration)]);
    $fake->scenes('clip.mp4', $changes);

    $cuts = app(SelectReelClips::class)->handle($video, Media::fromDisk('media')->open('clip.mp4'));

    return array_map(fn (Clip $cut): array => [$cut->from, $cut->to], $cuts);
}

it('cuts the start of scenes, spread over the video and in time order', function () {
    $cuts = selectReelCuts(Video::factory()->create(), 200, [20.0, 22.0, 60.0, 100.0, 140.0]);

    expect($cuts)->toBe([
        [20.5, 22.0],
        [60.5, 64.5],
        [100.5, 104.5],
        [140.5, 144.5],
    ]);
});

it('keeps cuts out of skippable chapters and the edges of the video', function () {
    $video = Video::factory()->create();
    $video->chapters()->create(['type' => ChapterType::Credits, 'label' => 'Credits', 'start_time' => 150, 'end_time' => 180]);
    $video->chapters()->create(['type' => ChapterType::Scene, 'label' => 'Fight', 'start_time' => 60, 'end_time' => 100]);

    $cuts = selectReelCuts($video, 200, [5.0, 40.0, 60.0, 100.0, 160.0, 195.0]);

    expect($cuts)->toBe([
        [40.5, 44.5],
        [60.5, 64.5],
        [100.5, 104.5],
    ]);
});

it('cuts evenly spaced parts when the video has too few scene changes', function () {
    $cuts = selectReelCuts(Video::factory()->create(), 200, [100.0]);

    expect($cuts)->toHaveCount(SelectReelClips::MAXIMUM_CUTS)
        ->and($cuts[0])->toBe([19.25, 23.25])
        ->and($cuts[7])->toBe([176.75, 180.75]);
});

it('limits a reel to eight cuts', function () {
    $cuts = selectReelCuts(Video::factory()->create(), 2000, range(210.0, 1890.0, 210.0));

    expect($cuts)->toHaveCount(SelectReelClips::MAXIMUM_CUTS);
});

it('uses short videos whole', function () {
    expect(selectReelCuts(Video::factory()->create(), 10, [5.0]))->toBe([[0.0, 10.0]]);
});

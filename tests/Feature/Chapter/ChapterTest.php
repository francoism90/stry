<?php

declare(strict_types=1);

use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a chapter with required attributes', function () {
    $video = Video::factory()->create();

    $chapter = Chapter::factory()->create([
        'video_id' => $video->getKey(),
        'type' => ChapterType::Intro,
        'label' => 'Intro',
        'start_time' => 0,
        'end_time' => 30,
    ]);

    expect($chapter->exists)->toBeTrue()
        ->and($chapter->video_id)->toBe($video->getKey())
        ->and($chapter->type)->toBe(ChapterType::Intro)
        ->and($chapter->label)->toBe('Intro')
        ->and((float) $chapter->start_time)->toBe(0.0)
        ->and((float) $chapter->end_time)->toBe(30.0);
});

it('belongs to a video', function () {
    $video = Video::factory()->create();

    $chapter = Chapter::factory()->create([
        'video_id' => $video->getKey(),
    ]);

    expect($chapter->video)->toBeInstanceOf(Video::class)
        ->and($chapter->video->getKey())->toBe($video->getKey());
});

it('uses ULIDs as identifiers', function () {
    $chapter = Chapter::factory()->create();

    expect($chapter->ulid)->not->toBeNull()
        ->and($chapter->getRouteKeyName())->toBe('ulid')
        ->and($chapter->getRouteKey())->toBe($chapter->ulid);
});

it('is not deleted when its video is soft deleted', function () {
    $video = Video::factory()->create();
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $video->delete();

    expect(Chapter::query()->find($chapter->getKey()))->not->toBeNull();
});

it('is deleted when its video is force deleted', function () {
    $video = Video::factory()->create();
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $video->forceDelete();

    expect(Chapter::query()->find($chapter->getKey()))->toBeNull();
});

it('exposes chapters on the video ordered by sort then start time', function () {
    $video = Video::factory()->create();

    $second = Chapter::factory()->create(['video_id' => $video->getKey(), 'sort' => 0, 'start_time' => 90, 'end_time' => 120]);
    $first = Chapter::factory()->create(['video_id' => $video->getKey(), 'sort' => 0, 'start_time' => 0, 'end_time' => 30]);
    $third = Chapter::factory()->create(['video_id' => $video->getKey(), 'sort' => 1, 'start_time' => 30, 'end_time' => 60]);

    expect($video->chapters->pluck('id')->all())->toBe([
        $first->getKey(),
        $second->getKey(),
        $third->getKey(),
    ]);
});

it('finds the skippable chapter that contains a given time', function () {
    $video = Video::factory()->create();

    Chapter::factory()->intro()->create(['video_id' => $video->getKey(), 'start_time' => 0, 'end_time' => 30]);
    Chapter::factory()->create(['video_id' => $video->getKey(), 'type' => ChapterType::Scene, 'start_time' => 30, 'end_time' => 60]);

    $video->load('chapters');

    expect($video->getSkippableChapterAt(15)?->type)->toBe(ChapterType::Intro)
        ->and($video->getSkippableChapterAt(45))->toBeNull()
        ->and($video->getSkippableChapterAt(30)?->type)->toBeNull();
});

it('treats the chapter start as inclusive and the end as exclusive', function () {
    $video = Video::factory()->create();

    Chapter::factory()->intro()->create(['video_id' => $video->getKey(), 'start_time' => 0, 'end_time' => 30]);

    $video->load('chapters');

    expect($video->getSkippableChapterAt(0))->not->toBeNull()
        ->and($video->getSkippableChapterAt(30))->toBeNull();
});

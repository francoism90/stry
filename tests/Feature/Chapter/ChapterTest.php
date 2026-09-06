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

it('exposes chapters on the video ordered by their auto-incrementing sort value', function () {
    $video = Video::factory()->create();

    $first = Chapter::factory()->create(['video_id' => $video->getKey()]);
    $second = Chapter::factory()->create(['video_id' => $video->getKey()]);
    $third = Chapter::factory()->create(['video_id' => $video->getKey()]);

    expect($video->chapters->pluck('id')->all())->toBe([
        $first->getKey(),
        $second->getKey(),
        $third->getKey(),
    ]);
});

it('can be reordered within its video via the sortable trait', function () {
    $video = Video::factory()->create();

    $first = Chapter::factory()->create(['video_id' => $video->getKey()]);
    $second = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $second->moveOrderUp();

    expect($video->chapters()->get()->pluck('id')->all())->toBe([
        $second->getKey(),
        $first->getKey(),
    ]);
});

it('scopes the auto-incrementing sort value to its own video', function () {
    $videoA = Video::factory()->create();
    $videoB = Video::factory()->create();

    $a1 = Chapter::factory()->create(['video_id' => $videoA->getKey()]);
    $b1 = Chapter::factory()->create(['video_id' => $videoB->getKey()]);
    $a2 = Chapter::factory()->create(['video_id' => $videoA->getKey()]);

    expect($a1->sort)->toBe(1)
        ->and($b1->sort)->toBe(1)
        ->and($a2->sort)->toBe(2);
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

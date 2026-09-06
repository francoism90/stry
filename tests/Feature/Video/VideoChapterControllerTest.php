<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoChapterController;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows the video owner to create a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->post(action([VideoChapterController::class, 'store'], $video), [
        'label' => 'Scene Two',
        'type' => 'scene',
        'start_time' => 120,
        'end_time' => 180,
    ]);

    $response->assertRedirect();

    expect(Chapter::query()->where('video_id', $video->getKey())->first())
        ->label->toBe('Scene Two')
        ->type->toBe(ChapterType::Scene);
});

it('classifies the chapter type from the label when none is given', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->post(action([VideoChapterController::class, 'store'], $video), [
        'label' => 'Introduction',
        'start_time' => 0,
        'end_time' => 30,
    ]);

    expect(Chapter::query()->where('video_id', $video->getKey())->first()->type)
        ->toBe(ChapterType::Intro);
});

it('does not overwrite an explicit type with classification', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->post(action([VideoChapterController::class, 'store'], $video), [
        'label' => 'Introduction',
        'type' => 'scene',
        'start_time' => 0,
        'end_time' => 30,
    ]);

    expect(Chapter::query()->where('video_id', $video->getKey())->first()->type)
        ->toBe(ChapterType::Scene);
});

it('prevents a non-owner from creating a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->post(action([VideoChapterController::class, 'store'], $video), [
        'label' => 'Scene Two',
        'start_time' => 120,
        'end_time' => 180,
    ]);

    $response->assertForbidden();
});

it('rejects an end time before the start time', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->post(action([VideoChapterController::class, 'store'], $video), [
        'label' => 'Scene Two',
        'start_time' => 180,
        'end_time' => 120,
    ]);

    $response->assertInvalid(['end_time']);
});

it('allows the video owner to update a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey(), 'label' => 'Old label']);

    $response = $this->actingAs($user)->put(action([VideoChapterController::class, 'update'], [$video, $chapter]), [
        'label' => 'New label',
    ]);

    $response->assertRedirect();

    expect($chapter->fresh()->label)->toBe('New label');
});

it('prevents a non-owner from updating a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $response = $this->actingAs($user)->put(action([VideoChapterController::class, 'update'], [$video, $chapter]), [
        'label' => 'New label',
    ]);

    $response->assertForbidden();

    expect($chapter->fresh()->label)->not->toBe('New label');
});

it('allows the video owner to delete a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $response = $this->actingAs($user)->delete(action([VideoChapterController::class, 'destroy'], [$video, $chapter]));

    $response->assertRedirect();

    expect(Chapter::query()->find($chapter->getKey()))->toBeNull();
});

it('prevents a non-owner from deleting a chapter', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    $chapter = Chapter::factory()->create(['video_id' => $video->getKey()]);

    $response = $this->actingAs($user)->delete(action([VideoChapterController::class, 'destroy'], [$video, $chapter]));

    $response->assertForbidden();

    expect(Chapter::query()->find($chapter->getKey()))->not->toBeNull();
});

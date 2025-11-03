<?php

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a video', function () {
    $video = Video::factory()->create([
        'title' => 'Test Video',
        'description' => 'Test Description',
    ]);

    expect($video->title)->toBe('Test Video')
        ->and($video->description)->toBe('Test Description')
        ->and($video->exists)->toBeTrue();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->id]);

    expect($video->user)->toBeInstanceOf(User::class)
        ->and($video->user->id)->toBe($user->id);
});

it('has required fields', function () {
    $video = Video::factory()->create();

    expect($video->title)->not->toBeNull()
        ->and($video->user_id)->not->toBeNull();
});

it('can be soft deleted', function () {
    $video = Video::factory()->create();
    $videoId = $video->id;

    $video->delete();

    expect(Video::find($videoId))->toBeNull()
        ->and(Video::withTrashed()->find($videoId))->not->toBeNull()
        ->and(Video::withTrashed()->find($videoId)->trashed())->toBeTrue();
});

it('can be restored after soft delete', function () {
    $video = Video::factory()->create();
    $videoId = $video->id;

    $video->delete();
    $video->restore();

    expect(Video::find($videoId))->not->toBeNull()
        ->and(Video::find($videoId)->trashed())->toBeFalse();
});

it('can be force deleted', function () {
    $video = Video::factory()->create();
    $videoId = $video->id;

    $video->forceDelete();

    expect(Video::withTrashed()->find($videoId))->toBeNull();
});

it('filters videos by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Video::factory()->count(3)->create(['user_id' => $user1->id]);
    Video::factory()->count(2)->create(['user_id' => $user2->id]);

    $user1Videos = Video::where('user_id', $user1->id)->get();
    $user2Videos = Video::where('user_id', $user2->id)->get();

    expect($user1Videos)->toHaveCount(3)
        ->and($user2Videos)->toHaveCount(2);
});

it('can update video attributes', function () {
    $video = Video::factory()->create([
        'title' => 'Original Title',
    ]);

    $video->update([
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    expect($video->fresh()->title)->toBe('Updated Title')
        ->and($video->fresh()->description)->toBe('Updated Description');
});

it('has timestamps', function () {
    $video = Video::factory()->create();

    expect($video->created_at)->not->toBeNull()
        ->and($video->updated_at)->not->toBeNull();
});

it('can retrieve all videos', function () {
    Video::factory()->count(5)->create();

    $videos = Video::all();

    expect($videos)->toHaveCount(5);
});

it('can search videos by title', function () {
    Video::factory()->create(['title' => 'Laravel Tutorial']);
    Video::factory()->create(['title' => 'Vue.js Guide']);
    Video::factory()->create(['title' => 'Laravel Advanced']);

    $results = Video::where('title', 'like', '%Laravel%')->get();

    expect($results)->toHaveCount(2);
});

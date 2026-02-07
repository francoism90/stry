<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a video with required attributes', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create([
        'user_id' => $user->id,
        'name' => ['en' => 'Test Video'],
        'summary' => ['en' => 'Test Summary'],
    ]);

    expect($video->exists)->toBeTrue()
        ->and($video->name)->toBe('Test Video')
        ->and($video->summary)->toBe('Test Summary')
        ->and($video->user_id)->toBe($user->id);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->id]);

    expect($video->user)->toBeInstanceOf(User::class)
        ->and($video->user->id)->toBe($user->id);
});

it('has required fields', function () {
    $video = Video::factory()->create();

    expect($video->name)->not->toBeNull()
        ->and($video->user_id)->not->toBeNull()
        ->and($video->state)->not->toBeNull()
        ->and($video->published_at)->not->toBeNull();
});

it('uses ULIDs as identifiers', function () {
    $video = Video::factory()->create();

    expect($video->ulid)->not->toBeNull()
        ->and($video->getRouteKeyName())->toBe('ulid')
        ->and($video->getRouteKey())->toBe($video->ulid);
});

it('has verified state by default', function () {
    $video = Video::factory()->create();

    expect($video->state)->toBeInstanceOf(Verified::class)
        ->and($video->isValid())->toBeTrue();
});

it('can be soft deleted', function () {
    $video = Video::factory()->create();
    $id = $video->getKey();

    $video->delete();

    expect(Video::find($id))->toBeNull()
        ->and(Video::withTrashed()->find($id))->not->toBeNull()
        ->and(Video::withTrashed()->find($id)->trashed())->toBeTrue();
});

it('can be restored after soft delete', function () {
    $video = Video::factory()->create();
    $id = $video->getKey();

    $video->delete();
    Video::withTrashed()->find($id)->restore();

    expect(Video::find($id))->not->toBeNull()
        ->and(Video::find($id)->trashed())->toBeFalse();
});

it('can be force deleted', function () {
    $video = Video::factory()->create();
    $id = $video->getKey();

    $video->forceDelete();

    expect(Video::withTrashed()->find($id))->toBeNull();
});

it('can filter videos by user', function () {
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
        'name' => ['en' => 'Original Name'],
        'summary' => ['en' => 'Original Summary'],
    ]);

    $video->update([
        'name' => ['en' => 'Updated Name'],
        'summary' => ['en' => 'Updated Summary'],
    ]);

    expect($video->fresh()->name)->toBe('Updated Name')
        ->and($video->fresh()->summary)->toBe('Updated Summary');
});

it('has timestamps', function () {
    $video = Video::factory()->create();

    expect($video->created_at)->not->toBeNull()
        ->and($video->updated_at)->not->toBeNull()
        ->and($video->published_at)->not->toBeNull();
});

it('supports translatable fields', function () {
    $video = Video::factory()->create([
        'name' => [
            'en' => 'English Title',
            'es' => 'Spanish Title',
        ],
        'summary' => [
            'en' => 'English Summary',
            'es' => 'Spanish Summary',
        ],
    ]);

    expect($video->getTranslation('name', 'en'))->toBe('English Title')
        ->and($video->getTranslation('name', 'es'))->toBe('Spanish Title')
        ->and($video->getTranslation('summary', 'en'))->toBe('English Summary')
        ->and($video->getTranslation('summary', 'es'))->toBe('Spanish Summary');
});

it('generates title attribute correctly', function () {
    $video = Video::factory()->create([
        'name' => ['en' => 'Test Video'],
        'season' => null,
        'episode' => null,
        'part' => null,
    ]);

    expect($video->title)->toBe('Test Video');

    $videoWithMetadata = Video::factory()->create([
        'name' => ['en' => 'Episode Name'],
        'season' => 1,
        'episode' => 5,
        'part' => 2,
    ]);

    expect($videoWithMetadata->identifier)->toBe('15')
        ->and($videoWithMetadata->title)->toBe('15 | Episode Name | 2');
});

it('can have tags', function () {
    $video = Video::factory()->create();

    $video->attachTag('Documentary');
    $video->attachTag('Nature');

    $video->refresh();

    expect($video->tags)->toHaveCount(2)
        ->and($video->tags->pluck('name')->toArray())->toContain('Documentary', 'Nature');
});

it('hides sensitive attributes', function () {
    $video = Video::factory()->create();

    $array = $video->toArray();

    expect($array)->not->toHaveKey('user_id');
});

it('can check if video is expired', function () {
    $expiredVideo = Video::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    $validVideo = Video::factory()->create([
        'expires_at' => now()->addDay(),
    ]);

    $neverExpiresVideo = Video::factory()->create([
        'expires_at' => null,
    ]);

    expect($expiredVideo->isExpired())->toBeTrue()
        ->and($validVideo->isExpired())->toBeFalse()
        ->and($neverExpiresVideo->isExpired())->toBeFalse();
});

it('casts adult flag as boolean', function () {
    $video = Video::factory()->create(['adult' => true]);
    $nonAdultVideo = Video::factory()->create(['adult' => false]);

    expect($video->adult)->toBeTrue()
        ->and($nonAdultVideo->adult)->toBeFalse();
});

it('casts snapshot as decimal', function () {
    $video = Video::factory()->create(['snapshot' => 12.5]);

    expect($video->snapshot)->toBe('12.50');
});

it('can store seasonal information', function () {
    $video = Video::factory()->create([
        'season' => 2,
        'episode' => 10,
        'part' => 1,
    ]);

    expect($video->season)->toBe(2)
        ->and($video->episode)->toBe(10)
        ->and($video->part)->toBe(1);
});

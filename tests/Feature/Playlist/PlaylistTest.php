<?php

declare(strict_types=1);

use Domain\Playlists\Models\Playlist;
use Domain\Playlists\States\Failed;
use Domain\Playlists\States\Pending;
use Domain\Playlists\States\Verified;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a playlist with required attributes', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $playlist = Playlist::factory()->create([
        'user_id' => $user->getKey(),
        'playlistable_id' => $video->getKey(),
        'file_name' => 'playlist.m3u8',
    ]);

    expect($playlist->exists)->toBeTrue()
        ->and($playlist->user_id)->toBe($user->getKey())
        ->and($playlist->playlistable_id)->toBe($video->getKey())
        ->and($playlist->playlistable_type)->toBe(Video::class)
        ->and($playlist->disk)->toBe('segments')
        ->and($playlist->file_name)->toBe('playlist.m3u8')
        ->and($playlist->secret_disk)->toBe('secrets');
});

it('belongs to a user', function () {
    $user = User::factory()->create();

    $playlist = Playlist::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    expect($playlist->user)->toBeInstanceOf(User::class)
        ->and($playlist->user->getKey())->toBe($user->getKey());
});

it('has a polymorphic playlistable relationship', function () {
    $video = Video::factory()->create();

    $playlist = Playlist::factory()->create([
        'playlistable_id' => $video->getKey(),
    ]);

    expect($playlist->playlistable)->toBeInstanceOf(Video::class)
        ->and($playlist->playlistable->getKey())->toBe($video->getKey());
});

it('uses ULIDs as identifiers', function () {
    $playlist = Playlist::factory()->create();

    expect($playlist->ulid)->not->toBeNull()
        ->and($playlist->getRouteKeyName())->toBe('ulid')
        ->and($playlist->getRouteKey())->toBe($playlist->ulid);
});

it('has pending state by default', function () {
    $playlist = Playlist::factory()->create();

    expect($playlist->state)->toBeInstanceOf(Pending::class);
});

it('can transition to verified state', function () {
    $playlist = Playlist::factory()->verified()->create();

    expect($playlist->state)->toBeInstanceOf(Verified::class)
        ->and($playlist->isValid())->toBeTrue()
        ->and($playlist->transcoded_at)->not->toBeNull();
});

it('can transition to failed state', function () {
    $playlist = Playlist::factory()->failed()->create();

    expect($playlist->state)->toBeInstanceOf(Failed::class)
        ->and($playlist->isValid())->toBeFalse();
});

it('casts progress as array object', function () {
    $playlist = Playlist::factory()->withProgress(50)->create();

    expect($playlist->progress)->toBeInstanceOf(ArrayObject::class)
        ->and($playlist->progress['percentage'])->toBe(50)
        ->and($playlist->getPercentage())->toBe(50.0);
});

it('can check if playlist is expired', function () {
    $expiredPlaylist = Playlist::factory()->expired()->create();
    $validPlaylist = Playlist::factory()->create(['expires_at' => now()->addDay()]);
    $neverExpiresPlaylist = Playlist::factory()->create(['expires_at' => null]);

    expect($expiredPlaylist->isExpired())->toBeTrue()
        ->and($validPlaylist->isExpired())->toBeFalse()
        ->and($neverExpiresPlaylist->isExpired())->toBeFalse();
});

it('has timestamps', function () {
    $playlist = Playlist::factory()->create();

    expect($playlist->created_at)->not->toBeNull()
        ->and($playlist->updated_at)->not->toBeNull();
});

it('can update playlist attributes', function () {
    $playlist = Playlist::factory()->create();

    $playlist->update([
        'state' => Verified::class,
        'transcoded_at' => now(),
    ]);

    expect($playlist->fresh()->state)->toBeInstanceOf(Verified::class)
        ->and($playlist->fresh()->transcoded_at)->not->toBeNull();
});

it('hides sensitive attributes', function () {
    $playlist = Playlist::factory()->create();

    $array = $playlist->toArray();

    expect($array)->not->toHaveKey('user_id');
});

it('can get disk and secret disk', function () {
    $playlist = Playlist::factory()->create();

    expect($playlist->getDisk())->toBe('segments')
        ->and($playlist->getSecretDisk())->toBe('secrets');
});

it('can get path', function () {
    $playlist = Playlist::factory()->create();

    $path = $playlist->getPath('segment-001.ts');

    expect($path)->toContain((string) $playlist->getKey())
        ->and($path)->toContain('segment-001.ts');
});

it('can get model from playlistable', function () {
    $video = Video::factory()->create();

    $playlist = Playlist::factory()->create([
        'playlistable_id' => $video->getKey(),
    ]);

    expect($playlist->getModel())->toBeInstanceOf(Video::class)
        ->and($playlist->getModel()->getKey())->toBe($video->getKey());
});

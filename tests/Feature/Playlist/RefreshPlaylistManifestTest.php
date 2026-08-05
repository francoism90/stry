<?php

declare(strict_types=1);

use Domain\Playlists\Actions\RefreshPlaylistManifest;
use Domain\Playlists\Models\Playlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('touches a verified playlist to re-broadcast a freshly signed manifest url', function () {
    $playlist = Playlist::factory()->verified()->create(['updated_at' => now()->subHour()]);
    $originalUpdatedAt = $playlist->updated_at;

    app(RefreshPlaylistManifest::class)->handle($playlist);

    expect($playlist->fresh()->updated_at)->not->toEqual($originalUpdatedAt)
        ->and($playlist->modelCacheHas('manifest-fresh'))->toBeTrue();
});

it('does not refresh again while the freshness window is still active', function () {
    $playlist = Playlist::factory()->verified()->create();

    app(RefreshPlaylistManifest::class)->handle($playlist);
    $refreshedAt = $playlist->fresh()->updated_at;

    app(RefreshPlaylistManifest::class)->handle($playlist);

    expect($playlist->fresh()->updated_at)->toEqual($refreshedAt);
});

it('refreshes again once the freshness window has elapsed', function () {
    config()->set('playlists.manifest_url_lifetime', 600);
    config()->set('playlists.manifest_refresh_before', 300);

    $playlist = Playlist::factory()->verified()->create();

    app(RefreshPlaylistManifest::class)->handle($playlist);
    $firstRefresh = $playlist->fresh()->updated_at;

    $this->travel(301)->seconds();

    app(RefreshPlaylistManifest::class)->handle($playlist);

    expect($playlist->fresh()->updated_at)->not->toEqual($firstRefresh);
});

it('does not refresh a playlist that is not verified', function () {
    $playlist = Playlist::factory()->create();
    $originalUpdatedAt = $playlist->updated_at;

    app(RefreshPlaylistManifest::class)->handle($playlist);

    expect($playlist->fresh()->updated_at)->toEqual($originalUpdatedAt)
        ->and($playlist->modelCacheHas('manifest-fresh'))->toBeFalse();
});

<?php

declare(strict_types=1);

use Domain\Playlists\Models\Playlist;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('refreshes the manifest of the video being watched', function () {
    $video = Video::factory()->create();
    $playlist = Playlist::factory()->verified()->create([
        'playlistable_id' => $video->getKey(),
        'updated_at' => now()->subHour(),
    ]);
    $originalUpdatedAt = $playlist->updated_at;

    VideoHasBeenViewedEvent::dispatch($video, null, ['time' => 12.5]);

    expect($playlist->fresh()->updated_at)->not->toEqual($originalUpdatedAt);
});

it('does nothing when the video has no playlist', function () {
    $video = Video::factory()->create();

    VideoHasBeenViewedEvent::dispatch($video, null, ['time' => 12.5]);

    expect($video->getPlaylist())->toBeNull();
});

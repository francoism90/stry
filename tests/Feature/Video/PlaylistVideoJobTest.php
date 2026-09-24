<?php

declare(strict_types=1);

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;

it('uses the configured playlist type for the unique id when no type is given', function () {
    PlaylistSettings::fake(['type' => PlaylistType::Streamer]);

    $video = Video::factory()->create();

    $implicit = new PlaylistVideo($video);

    expect($implicit->uniqueId())
        ->toBe((new PlaylistVideo($video, PlaylistType::Streamer))->uniqueId())
        ->not->toBe((new PlaylistVideo($video, PlaylistType::Packager))->uniqueId());
});

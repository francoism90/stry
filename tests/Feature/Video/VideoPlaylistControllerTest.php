<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoPlaylistController;
use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('segments'));

it('deletes a playlist that has no stored type', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $user->getKey()]);

    $playlist = Playlist::factory()->create([
        'user_id' => $user->getKey(),
        'playlistable_id' => $video->getKey(),
        'type' => null,
    ]);

    $response = $this->actingAs($user)->delete(action([VideoPlaylistController::class, 'destroy'], [$video, $playlist]));

    $response->assertRedirect();

    $this->assertModelMissing($playlist);
});

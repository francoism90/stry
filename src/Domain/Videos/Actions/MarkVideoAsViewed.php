<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class MarkVideoAsViewed
{
    public function execute(User $user, Video $video): void
    {
        DB::transaction(function () use ($video) {
            // Playlist the video (if needed)
            PlaylistVideo::dispatchIf(! $video->currentPlaylist(), $video);
        });
    }
}

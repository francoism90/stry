<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;

class RefreshPlaylistManifest
{
    public function handle(Playlist $playlist): void
    {
        if (! $playlist->isValid() || $playlist->modelCacheHas('manifest-fresh')) {
            return;
        }

        $ttl = max(Playlist::getManifestUrlLifetime() - Playlist::getManifestRefreshBefore(), 0);

        $playlist->modelCache('manifest-fresh', true, now()->addSeconds($ttl));

        // Touching the playlist re-broadcasts it, so viewers pick up a freshly signed manifest URL
        $playlist->touch();
    }
}

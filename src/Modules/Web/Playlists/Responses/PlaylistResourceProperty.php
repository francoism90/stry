<?php

declare(strict_types=1);

namespace Modules\Web\Playlists\Responses;

use Domain\Playlists\Models\Playlist;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Playlists\Resources\PlaylistResource;

readonly class PlaylistResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Playlist $playlist = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?PlaylistResource => $this->getResource());
    }

    protected function getResource(): ?PlaylistResource
    {
        if (! $this->playlist) {
            return null;
        }

        return PlaylistResource::make($this->playlist->loadMissing('playlistable'));
    }
}

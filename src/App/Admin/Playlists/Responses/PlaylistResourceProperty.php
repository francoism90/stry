<?php

declare(strict_types=1);

namespace App\Admin\Playlists\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Models\Playlist;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

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

        return $this
            ->playlist
            ->toResource(PlaylistResource::class);
    }
}

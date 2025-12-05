<?php

declare(strict_types=1);

namespace App\Admin\Playlists\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Models\Playlist;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class PlaylistResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[RouteParameter('playlist')] protected Playlist $playlist,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): PlaylistResource => $this->getResource());
    }

    protected function getResource(): PlaylistResource
    {
        return $this->playlist
            ->toResource(PlaylistResource::class);
    }
}

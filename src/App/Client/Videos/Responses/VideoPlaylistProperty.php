<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoPlaylistProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?PlaylistResource => $this->getPlaylist());
    }

    protected function getPlaylist(): ?PlaylistResource
    {
        return $this->video
            ?->getFirstPlaylist('clip')
            ?->toResource(PlaylistResource::class);
    }
}

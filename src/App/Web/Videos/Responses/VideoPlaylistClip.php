<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoPlaylistClip implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Video $video,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return $this->video
            ?->getFirstPlaylist('clip')
            ?->toResource(PlaylistResource::class);
    }
}

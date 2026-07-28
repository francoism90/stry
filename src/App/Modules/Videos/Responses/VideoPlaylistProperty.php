<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Modules\Playlists\Resources\PlaylistResource;
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
        if (! $this->video || ! $this->video->hasPlaylist()) {
            return null;
        }

        return $this->video
            ->getPlaylist()
            ->toResource(PlaylistResource::class);
    }
}

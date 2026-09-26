<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Responses;

use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Playlists\Resources\PlaylistResource;

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
        if (! $this->video || ! $this->video->hasPlaylist()) {
            return null;
        }

        return PlaylistResource::make($this->video->getPlaylist());
    }
}

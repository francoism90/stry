<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Responses;

use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Playlists\Resources\PlaylistResource;
use Modules\Api\Videos\Resources\VideoDirectPlayResource;

readonly class VideoPlaylistProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): PlaylistResource|VideoDirectPlayResource|null => $this->getPlaylist());
    }

    protected function getPlaylist(): PlaylistResource|VideoDirectPlayResource|null
    {
        if ($this->video && app(PlaylistSettings::class)->isDirectPlay() && $this->video->hasMedia('clips')) {
            return VideoDirectPlayResource::make($this->video);
        }

        if (! $this->video || ! $this->video->hasPlaylist()) {
            return null;
        }

        return PlaylistResource::make($this->video->getPlaylist());
    }
}

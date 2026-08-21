<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoPlaylistsProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
        protected ?int $limit = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn () => $this->getCollection());
    }

    protected function getCollection(): ResourceCollection
    {
        if (! $this->video || Gate::denies('viewAny', Playlist::class)) {
            return PlaylistResource::collection([]);
        }

        return $this->video
            ->playlists()
            ->latest()
            ->limit($this->limit ?? 10)
            ->get()
            ->toResourceCollection(PlaylistResource::class);
    }
}

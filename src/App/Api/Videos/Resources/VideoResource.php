<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Chapters\Resources\ChapterResource;
use App\Api\Media\Resources\MediaResource;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Api\Tags\Resources\TagResource;
use App\Api\Transcodes\Resources\TranscodeResource;
use App\Api\Users\Resources\UserResource;
use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Video
 */
class VideoResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * @var Collection<int, GroupType>|null
     */
    protected ?Collection $groupTypes = null;

    public function toArray($request): array
    {
        $groupTypes = $this->resolveGroupTypes($request);

        return [
            'id' => $this->getRouteKey(),
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'identifier' => $this->identifier,
            'season' => $this->season,
            'episode' => $this->episode,
            'part' => $this->part,
            'released' => $this->released,
            'adult' => $this->adult,
            'captioned' => $this->captioned,
            'thumb' => $this->thumb,
            'thumb_srcset' => $this->thumb_srcset,
            'storyboard_image' => $this->storyboard_image,
            'storyboard_vtt' => $this->storyboard_vtt,
            'chapters_vtt' => $this->chapters_vtt,
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'liked' => $groupTypes?->contains(GroupType::Liked),
            'saved' => $groupTypes?->contains(GroupType::Saved),
            'viewed' => $groupTypes?->contains(GroupType::Viewed),
            'manage' => $request->user()?->can('update', $this->resource) ?? false,
            'titles' => $this->whenAppended('titles'),
            'summary' => $this->whenAppended('summary'),
            'content' => $this->whenAppended('content'),
            'filesize' => $this->whenAppended('filesize'),
            'codec' => $this->whenAppended('codec'),
            'resolution' => $this->whenAppended('resolution'),
            'bitrate' => $this->whenAppended('bitrate'),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'playlists' => PlaylistResource::collection($this->whenLoaded('playlists')),
            'transcodes' => TranscodeResource::collection($this->whenLoaded('transcodes')),
            'chapters' => ChapterResource::collection($this->whenLoaded('chapters')),
            'snapshot' => $this->whenAppended('snapshot'),
            'state' => $this->state->toArray(),
            'published_at' => $this->published_at?->toDateTimeString(),
            'released_at' => $this->released_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    /**
     * @param  Collection<int, GroupType>  $groupTypes
     */
    public function withGroupTypes(Collection $groupTypes): static
    {
        $this->groupTypes = $groupTypes;

        return $this;
    }

    /**
     * @return Collection<int, GroupType>|null
     */
    protected function resolveGroupTypes(Request $request): ?Collection
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        return $this->groupTypes ??= $user
            ->groupTypesFor(Collection::make([$this->resource]))
            ->get($this->resource->getKey(), Collection::make());
    }

    protected static function newCollection($resource): VideoResourceCollection
    {
        return new VideoResourceCollection($resource, static::class);
    }
}

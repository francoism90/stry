<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Media\Resources\MediaResource;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Api\Tags\Resources\TagResource;
use App\Api\Transcodes\Resources\TranscodeResource;
use App\Api\Users\Resources\UserResource;
use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Video
 */
class VideoResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
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
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'liked' => $request->user()?->isInGroup($this->resource, GroupType::Liked),
            'saved' => $request->user()?->isInGroup($this->resource, GroupType::Saved),
            'viewed' => $request->user()?->isInGroup($this->resource, GroupType::Viewed),
            'titles' => $this->whenAppended('titles'),
            'summary' => $this->whenAppended('summary'),
            'content' => $this->whenAppended('content'),
            'filesize' => $this->whenAppended('filesize'),
            'codec' => $this->whenAppended('codec'),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'playlists' => PlaylistResource::collection($this->whenLoaded('playlists')),
            'transcodes' => TranscodeResource::collection($this->whenLoaded('transcodes')),
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
}

<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Tags\Resources\TagResource;
use App\Api\Users\Resources\UserResource;
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
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'liked' => $request->user()?->isLiked($this->resource),
            'saved' => $request->user()?->isSaved($this->resource),
            'viewed' => $request->user()?->isViewed($this->resource),
            'titles' => $this->whenAppended('titles'),
            'summary' => $this->whenAppended('summary'),
            'content' => $this->whenAppended('content'),
            'filesize' => $this->whenAppended('filesize'),
            'snapshot' => $this->whenAppended('snapshot'),
            'state' => $this->state->label(),
            'published_at' => $this->published_at?->toDateTimeString(),
            'released_at' => $this->released_at?->toDateTimeString(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

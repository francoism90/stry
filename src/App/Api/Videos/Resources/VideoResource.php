<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Tags\Resources\TagResource;
use App\Api\Users\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'season' => $this->season,
            'episode' => $this->episode,
            'part' => $this->part,
            'adult' => $this->adult,
            'captioned' => $this->captioned,
            'thumb' => $this->thumb,
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'favorite' => $request->user()?->isFavorite($this->resource),
            'saved' => $request->user()?->isSaved($this->resource),
            'viewed' => $request->user()?->isViewed($this->resource),
            'titles' => $this->whenAppended('titles'),
            'summary' => $this->whenAppended('summary'),
            'content' => $this->whenAppended('content'),
            'filesize' => $this->whenAppended('filesize'),
            'snapshot' => $this->whenAppended('snapshot'),
            'state' => $this->state->label(),
            'published_at' => $this->published_at,
            'released_at' => $this->released_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

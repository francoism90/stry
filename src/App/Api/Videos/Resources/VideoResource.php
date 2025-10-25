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
            'summary' => $this->summary,
            'titles' => $this->whenAppended('titles'),
            'content' => $this->whenAppended('content'),
            'season' => $this->season,
            'episode' => $this->episode,
            'part' => $this->part,
            'captions' => $this->captions,
            'thumb' => $this->thumb,
            'preview' => $this->preview,
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'snapshot' => $this->snapshot,
            'state' => $this->state->label(),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'released_at' => $this->released_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => UserResource::make($this->whenLoaded('user')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Tags\Resources\TagCollection;
use App\Api\Users\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'name' => $this->name,
            'summary' => $this->summary,
            'season' => $this->season,
            'episode' => $this->episode,
            'part' => $this->part,
            'thumbnail' => $this->thumbnail,
            'srcset' => $this->srcset,
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'content' => $this->whenAppended('content'),
            'titles' => $this->whenAppended('titles'),
            'released' => $this->released_at,
            'expires' => $this->expires_at,
            'published' => $this->published_at,
            'created' => $this->created_at,
            'updated' => $this->updated_at,
            'user' => UserResource::make($this->whenLoaded('user')),
            'tags' => TagCollection::make($this->whenLoaded('tags')),
        ];
    }
}

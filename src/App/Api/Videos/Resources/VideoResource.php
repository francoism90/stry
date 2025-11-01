<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use App\Api\Media\Resources\MediaResource;
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
            'titles' => $this->whenAppended('titles'),
            'summary' => $this->whenAppended('summary'),
            'content' => $this->whenAppended('content'),
            'season' => $this->season,
            'episode' => $this->episode,
            'part' => $this->part,
            'adult' => $this->adult,
            'captioned' => $this->captioned,
            'thumb' => $this->thumb,
            'released' => $this->released,
            'duration' => $this->duration,
            'timestamp' => $this->timestamp,
            'state' => $this->state->label(),
            'snapshot' => $this->whenAppended('snapshot'),
            'expires_at' => $this->whenAppended('expires_at'),
            'published_at' => $this->whenAppended('published_at'),
            'released_at' => $this->whenAppended('released_at'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'captions' => MediaResource::collection($this->whenAppended('captions', $this->captions)),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

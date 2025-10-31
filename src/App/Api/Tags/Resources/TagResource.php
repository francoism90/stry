<?php

declare(strict_types=1);

namespace App\Api\Tags\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'summary' => $this->summary,
            'category' => $this->category,
            'adult' => $this->adult,
            'description' => $this->whenAppended('description'),
            'type' => $this->whenAppended('type'),
            'related' => TagResource::collection($this->whenAppended('relates')),
            'videos' => $this->whenCounted('videos'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

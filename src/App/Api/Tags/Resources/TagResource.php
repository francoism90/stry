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
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->category,
            'type' => $this->type,
            'related' => $this->whenLoaded('related'),
            'videos' => $this->whenCounted('videos'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

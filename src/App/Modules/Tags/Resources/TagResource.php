<?php

declare(strict_types=1);

namespace App\Api\Tags\Resources;

use Domain\Tags\Models\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tag
 */
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
            'slug' => $this->slug,
            'summary' => $this->summary,
            'category' => $this->category,
            'type' => $this->type,
            'adult' => $this->adult,
            'description' => $this->whenAppended('description'),
            'videos' => $this->whenCounted('videos'),
            'related' => TagResource::collection($this->whenAppended('relates')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

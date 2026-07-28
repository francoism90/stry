<?php

declare(strict_types=1);

namespace Foundation\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Model
 */
class ModelResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'type' => $this->getMorphClass(),
            'slug' => $this->whenHas('slug'),
            'name' => $this->whenHas('name'),
            'label' => $this->whenHas('label'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

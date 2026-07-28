<?php

declare(strict_types=1);

namespace App\Api\Groups\Resources;

use Domain\Groups\Models\Group;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class GroupResource extends JsonResource
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
            'content' => $this->content,
            'type' => $this->type,
            'state' => $this->state->toArray(),
            'videos' => $this->whenCounted('groupables'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

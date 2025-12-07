<?php

declare(strict_types=1);

namespace App\Api\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->whenAppended('email'),
            'roles' => $this->whenLoaded('roles', $this->assigned_roles),
            'permissions' => $this->whenLoaded('permissions', $this->assigned_permissions),
            'state' => $this->state->label(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'avatar' => $this->whenAppended('avatar'),
            'name' => $this->whenAppended('name'),
            'email' => $this->whenAppended('email'),
            'email_verified' => $this->whenAppended('email_verified'),
            'permissions' => $this->whenLoaded('permissions', $this->getAllPermissions()->pluck('name')),
            'roles' => $this->whenLoaded('roles', $this->getRoleNames()),
            'created' => $this->whenAppended('created_at'),
            'updated' => $this->whenAppended('updated_at'),
        ];
    }
}

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
            'avatar' => $this->whenAppended('avatar'),
            'name' => $this->whenAppended('name'),
            $this->mergeWhen($request->user()?->can('update', $this), [
                'email' => $this->whenAppended('email'),
                'email_verified' => $this->whenAppended('email_verified'),
                'roles' => $this->whenLoaded('roles', $this->assigned_roles),
                'permissions' => $this->whenLoaded('permissions', $this->assigned_permissions),
                'state' => $this->whenAppended('state'),
            ]),
            'created_at' => $this->whenAppended('created_at'),
            'updated_at' => $this->whenAppended('updated_at'),
        ];
    }
}

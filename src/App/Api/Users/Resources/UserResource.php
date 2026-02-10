<?php

declare(strict_types=1);

namespace App\Api\Users\Resources;

use Domain\Users\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
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
            'avatar' => $this->whenAppended('avatar'),
            'email' => $this->whenAppended('email'),
            'roles' => $this->whenLoaded('roles', $this->assigned_roles),
            'permissions' => $this->whenLoaded('permissions', $this->assigned_permissions),
            'state' => $this->state->label(),
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Web\Users\Responses;

use App\Api\Users\Resources\UserResource;
use Domain\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class AuthenticatedProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        if (! $this->user) {
            return null;
        }

        return UserResource::make($this->user
            ->loadMissing('permissions', 'roles')
            ->append('name', 'avatar'),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Admin\Users\Responses;

use App\Api\Users\Resources\UserResource;
use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class UserResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected User $user,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): UserResource => $this->getResource());
    }

    protected function getResource(): UserResource
    {
        // Append necessary attributes for the edit form
        $appends = [
            'email',
        ];

        return $this->user
            ->loadMissing('roles', 'permissions')
            ->append($appends)
            ->toResource(UserResource::class);
    }
}

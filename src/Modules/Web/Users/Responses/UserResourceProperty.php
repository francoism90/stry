<?php

declare(strict_types=1);

namespace Modules\Web\Users\Responses;

use Domain\Users\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Users\Resources\UserResource;

readonly class UserResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
        protected ?array $appends = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?UserResource => $this->getResource());
    }

    protected function getResource(): ?UserResource
    {
        if (! $this->user) {
            return null;
        }

        if (Gate::allows('update', $this->user)) {
            $this->user->loadMissing('roles', 'permissions');
        }

        return UserResource::make($this->user->append($this->appends ?? []));
    }
}

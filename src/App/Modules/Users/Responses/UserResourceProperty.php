<?php

declare(strict_types=1);

namespace App\Modules\Users\Responses;

use App\Modules\Users\Resources\UserResource;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

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

        return $this->user
            ->append($this->appends ?? [])
            ->toResource(UserResource::class);
    }
}

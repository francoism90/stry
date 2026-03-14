<?php

declare(strict_types=1);

namespace App\Web\Groups\Responses;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Models\Group;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GroupResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Group $group = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?GroupResource => $this->getResource());
    }

    protected function getResource(): ?GroupResource
    {
        if (! $this->group) {
            return null;
        }

        return $this->group
            ->loadCount('groupables')
            ->toResource(GroupResource::class);
    }
}

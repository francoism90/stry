<?php

declare(strict_types=1);

namespace Modules\Web\Groups\Responses;

use Domain\Groups\Models\Group;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Groups\Resources\GroupResource;

readonly class GroupResourceProperty implements ProvidesInertiaProperty
{
    /**
     * @param  list<string>|null  $appends
     */
    public function __construct(
        protected ?Group $group = null,
        protected ?array $appends = null,
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

        return GroupResource::make($this->group->loadCount('groupables')->append($this->appends ?? []));
    }
}

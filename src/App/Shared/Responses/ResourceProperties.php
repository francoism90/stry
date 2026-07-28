<?php

declare(strict_types=1);

namespace App\Shared\Responses;

use Illuminate\Database\Eloquent\Model;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ResourceProperties implements ProvidesInertiaProperty
{
    public function __construct(
        protected Model|string|null $resource = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        /** @var Model|null $resource */
        $resource = $this->getResource();

        return [
            'id' => $resource?->getRouteKey(),
            'subject' => $resource?->getMorphClass(),
        ];
    }

    protected function getResource(): ?Model
    {
        return is_a($this->resource, Model::class, true)
            ? app($this->resource)
            : $this->resource;
    }
}

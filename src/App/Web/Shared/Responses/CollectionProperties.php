<?php

declare(strict_types=1);

namespace App\Web\Shared\Responses;

use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

class CollectionProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[RouteParameter('search')] protected ?string $search = null,
        #[QueryParameter('filter')] protected ?string $filter = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => fn () => $this->filter,
            'search' => fn () => $this->search,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Web\Shared\Responses;

use Foundation\Container\Attributes\QueryParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

class CollectionProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[QueryParameter('filter')] protected ?string $filter = null,
        #[QueryParameter('search')] protected ?string $search = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => fn () => $this->filter,
            'search' => fn () => $this->search,
        ];
    }
}

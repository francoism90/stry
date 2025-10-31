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
        #[QueryParameter('order')] protected ?string $order = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'order' => fn () => $this->order,
            'search' => fn () => $this->search,
        ];
    }
}

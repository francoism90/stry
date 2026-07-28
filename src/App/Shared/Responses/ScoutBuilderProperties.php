<?php

declare(strict_types=1);

namespace App\Shared\Responses;

use Foundation\Container\Attributes\RequestInputParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class ScoutBuilderProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[RequestInputParameter('filter')] protected ?array $filter = null,
        #[RequestInputParameter('sort')] protected ?string $sort = null,
        #[RequestInputParameter('query')] protected ?string $query = null,
        #[RequestInputParameter('page')] protected ?int $page = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => $this->filter,
            'sort' => $this->sort,
            'query' => $this->query,
            'page' => $this->page,
        ];
    }
}

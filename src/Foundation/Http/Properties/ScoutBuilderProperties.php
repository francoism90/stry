<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class ScoutBuilderProperties implements ProvidesInertiaProperties
{
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => $context->request->input('filter'),
            'sort' => $context->request->input('sort'),
            'query' => $context->request->input('query'),
        ];
    }
}

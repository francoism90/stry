<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class ScoutBuilderProperties implements ProvidesInertiaProperties
{
    public function toInertiaProperties(RenderContext $context): array
    {
        // Remember the search term for the global search bar
        if (($query = trim((string) $context->request->query('query', ''))) !== '') {
            $context->request->session()->cache()->put('search', $query, now()->addHour());
        }

        return [
            'query' => $context->request->input('query'),
            'filter' => $context->request->input('filter'),
            'sort' => $context->request->input('sort'),
            'page' => $context->request->input('page'),
        ];
    }
}

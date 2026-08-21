<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class ScoutBuilderProperties implements ProvidesInertiaProperties
{
    public function __construct(
        private string $scope,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        $request = $context->request;
        $key = "search.{$this->scope}";

        // Remember the search term for the global search bar, scoped per resource
        if (($query = trim((string) $request->query('query', ''))) !== '') {
            $request->session()->cache()->put($key, $query, now()->addHour());
        }

        return [
            'search' => $request->session()->cache()->get($key),
            'query' => $request->input('query'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort'),
            'page' => $request->input('page'),
        ];
    }
}

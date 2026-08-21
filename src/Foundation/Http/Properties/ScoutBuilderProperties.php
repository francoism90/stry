<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Illuminate\Http\Request;
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

        $this->rememberQuery($request, $key);

        return [
            'search' => $this->rememberedSearch($request, $key),
            'query' => $request->input('query'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort'),
            'page' => $request->input('page'),
        ];
    }

    // Session, not Cache::store('session'): under Octane, CacheManager keeps
    // resolved stores cached for the worker's lifetime, not per-request.
    private function rememberQuery(Request $request, string $key): void
    {
        // Only act when "query" was actually submitted, so a plain navigation
        // (no "query" param at all) leaves the remembered term untouched.
        if (! $request->has('query')) {
            return;
        }

        $query = trim((string) $request->query('query'));

        if (blank($query)) {
            $request->session()->forget($key);

            return;
        }

        $request->session()->put($key, [
            'term' => $query,
            'expires_at' => now()->addHour()->timestamp,
        ]);
    }

    private function rememberedSearch(Request $request, string $key): ?string
    {
        $remembered = $request->session()->get($key);

        if (! $remembered || $remembered['expires_at'] < now()->timestamp) {
            return null;
        }

        return $remembered['term'];
    }
}

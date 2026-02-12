<?php

declare(strict_types=1);

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Spatie\Csp\AddCspHeaders as Middleware;

class AddCspHeaders extends Middleware
{
    public function handle(
        Request $request,
        Closure $next,
        ?string $customPreset = null
    ): mixed {
        return $this->shouldSkipCsp($request)
            ? $next($request)
            : parent::handle($request, $next, $customPreset);
    }

    protected function shouldSkipCsp(Request $request): bool
    {
        return $request->routeIs(['boost.*', 'horizon.*', 'telescope.*']);
    }
}

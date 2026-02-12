<?php

declare(strict_types=1);

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Support\Facades\Vite;

class AddContentSecurityPolicyHeaders
{
    public function handle($request, Closure $next, $options = []): mixed
    {
        Vite::useCspNonce();

        return $next($request);
    }
}

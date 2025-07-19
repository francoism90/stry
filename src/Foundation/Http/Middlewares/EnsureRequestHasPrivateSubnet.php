<?php

declare(strict_types=1);

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class EnsureRequestHasPrivateSubnet extends Middleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(IpUtils::isPrivateIp($request->ip()), 403, 'Unauthorized');

        return $next($request);
    }
}

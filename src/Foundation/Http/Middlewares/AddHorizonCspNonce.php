<?php

declare(strict_types=1);

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use Symfony\Component\HttpFoundation\Response;

class AddHorizonCspNonce
{
    public function handle(Request $request, Closure $next): Response
    {
        Horizon::cspNonce(app('csp-nonce'));

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Laravel\Telescope\Telescope;
use Symfony\Component\HttpFoundation\Response;

class AddTelescopeCspNonce
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) config('telescope.enabled') && class_exists(Telescope::class)) {
            Telescope::cspNonce(app('csp-nonce'));
        }

        return $next($request);
    }
}

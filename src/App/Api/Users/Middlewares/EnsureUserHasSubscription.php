<?php

declare(strict_types=1);

namespace App\Api\Users\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureUserHasSubscription
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): mixed
    {
        if ($request->user() && $request->user()->hasValidSubscription()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, 'Your subscription plan is expired.')
            : Redirect::route($redirectToRoute ?: 'subscription.notice');
    }
}

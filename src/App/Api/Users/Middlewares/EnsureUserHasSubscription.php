<?php

declare(strict_types=1);

namespace App\Api\Users\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class EnsureUserHasSubscription
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): mixed
    {
        // TODO: check if user has a subscription

        if ($request->user()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, 'Your subscription plan is not verified.')
            : Redirect::guest(URL::route($redirectToRoute ?: 'subscription.notice'));
    }
}

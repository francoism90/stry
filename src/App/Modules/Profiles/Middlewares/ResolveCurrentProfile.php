<?php

declare(strict_types=1);

namespace App\Modules\Profiles\Middlewares;

use Closure;
use Domain\Profiles\Models\Profile;
use Domain\Profiles\Support\CurrentProfileContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $profile = null;

        // If the user is authenticated, attempt to resolve the current profile from the session or fallback to the user's current profile.
        if ($user) {
            $profile = Session::has('profiles.current')
                ? Profile::findFromUlid(Session::get('profiles.current'))
                : $user->currentProfile();
        }

        // Set the resolved profile on the request and the current profile context for global access.
        app(CurrentProfileContext::class)->set($profile);

        // Setting the current profile as a request attribute allows it to be easily accessed throughout the request lifecycle without needing to resolve it multiple times.
        $request->attributes->set('currentProfile', $profile);

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Web\Profiles\Middlewares;

use Closure;
use Domain\Profiles\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $profile = null;

        if ($user) {
            $profile = Session::has('profiles.current')
                ? Profile::findFromUlid(Session::get('profiles.current'))
                : $user->currentProfile();
        }

        $request->attributes->set('currentProfile', $profile);

        return $next($request);
    }
}

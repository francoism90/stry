<?php

declare(strict_types=1);

namespace App\Web\Profiles\Controllers;

use Domain\Profiles\Models\Profile;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request): Response
    {
        $currentProfile = (string) $request->session()->get('profiles.current', '');

        return Inertia::render('App/Profiles/ProfileIndex', [
            'profiles' => fn (): array => $request
                ->user()
                ->profiles()
                ->orderByDesc('is_primary')
                ->orderBy('name')
                ->get()
                ->map(fn (Profile $profile): array => [
                    'id' => (string) $profile->getRouteKey(),
                    'name' => $profile->name,
                    'avatar' => $profile->avatar,
                    'is_kids' => $profile->is_kids,
                    'is_primary' => $profile->is_primary,
                    'is_current' => $currentProfile === (string) $profile->getRouteKey(),
                    'state' => $profile->state->toArray(),
                ])
                ->values()
                ->all(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Web\Profiles\Controllers;

use App\Web\Profiles\Responses\ProfileCollectionProperty;
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
            'profiles' => fn (): ProfileCollectionProperty => new ProfileCollectionProperty(
                $request->user(),
                $currentProfile,
            ),
        ]);
    }
}

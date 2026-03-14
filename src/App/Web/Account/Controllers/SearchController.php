<?php

declare(strict_types=1);

namespace App\Client\Account\Controllers;

use App\Api\Users\Resources\UserResource;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', [Tag::class, Video::class]);

        return Inertia::render('App/Account/SearchIndex', [
            'user' => fn () => UserResource::make($request->user()->append('email', 'avatar')),
        ]);
    }
}

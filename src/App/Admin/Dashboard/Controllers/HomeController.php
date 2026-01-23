<?php

declare(strict_types=1);

namespace App\Admin\Dashboard\Controllers;

use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('Admin/DashboardIndex', [
            'videos' => Inertia::once(fn () => Video::count())->until(now()->addHour()),
            'tags' => Inertia::once(fn () => Tag::count())->until(now()->addHour()),
            'users' => Inertia::once(fn () => User::count())->until(now()->addHour()),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Admin\Users\Controllers;

use App\Api\Users\Requests\UserIndexRequest;
use App\Api\Users\Resources\UserResource;
use Domain\Users\Models\User;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(UserIndexRequest $request): Response
    {
        Gate::authorize('viewAny', User::class);

        // Apply filters
        $search = $request->safe()->input('search', '');

        // Scout builder
        $scout = User::search($search)
            ->query(fn ($query) => $query->with('permissions', 'roles'))
            ->simplePaginate(16)
            ->through(fn (User $user) => new UserResource($user));

        return Inertia::render('Admin/Users/UserIndex', [
            'items' => Inertia::scroll(fn () => $scout),
        ]);
    }
}

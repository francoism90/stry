<?php

declare(strict_types=1);

namespace App\Web\Users\Controllers;

use App\Api\Users\Requests\UserIndexRequest;
use App\Api\Users\Requests\UserUpdateRequest;
use App\Api\Users\Resources\UserResource;
use App\Web\Users\Responses\UserResourceProperty;
use Domain\Users\Models\User;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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
            new Middleware('precognitive'),
        ];
    }

    public function index(UserIndexRequest $request): Response
    {
        Gate::authorize('viewAny', User::class);

        // Apply filters
        $search = $request->safe()->input('search');

        // Scout builder
        $scout = User::search($search)
            ->query(fn ($query) => $query->with('permissions', 'roles'))
            ->simplePaginate(perPage: 16);

        return Inertia::render('Admin/Users/UserIndex', [
            'items' => Inertia::scroll(fn () => UserResource::collection($scout)),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/Users/UserEdit', [
            'user' => fn () => new UserResourceProperty(user: $user),
        ]);
    }

    public function update(User $user, UserUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $user);

        // Update user
        $user->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $user->name,
            'description' => __('The user has been updated.'),
        ]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        // Delete user
        $user->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $user->name,
            'description' => __('The user has been deleted.'),
        ]);

        return back();
    }
}

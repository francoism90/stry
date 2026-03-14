<?php

declare(strict_types=1);

namespace App\Web\Users\Controllers;

use App\Api\Users\Requests\UserUpdateRequest;
use Domain\Users\Models\User;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
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

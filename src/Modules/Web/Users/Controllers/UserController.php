<?php

declare(strict_types=1);

namespace Modules\Web\Users\Controllers;

use Domain\Users\Actions\CreateNewUser;
use Domain\Users\Actions\UpdateUserProfileInformation;
use Domain\Users\Enums\UserScope;
use Domain\Users\Enums\UserSorter;
use Domain\Users\Filters\UserScopeFilter;
use Domain\Users\Models\User;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Api\Users\Requests\UserStoreRequest;
use Modules\Api\Users\Requests\UserUpdateRequest;
use Modules\Api\Users\Resources\UserResource;
use Spatie\LaravelOptions\Options;

class UserController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        // Relevant sort options
        $defaultSort = AllowedSort::latest('newest', 'created_at');

        // Scout builder
        $scout = ScoutBuilder::for(User::class)
            ->query(function (Builder $query): void {
                $query->withCount('videos')->with('roles', 'permissions');
            })
            ->allowedFilters(
                AllowedFilter::custom('scope', new UserScopeFilter),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::oldest('oldest', 'created_at'),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 16);

        collect($scout->items())->each(fn (User $user) => $user->append(['name', 'email', 'avatar']));

        return Inertia::render('Users/UserIndex', [
            'items' => Inertia::scroll(fn () => UserResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(UserScope::class),
            'sorters' => fn () => Options::forEnum(UserSorter::class),
            new ScoutBuilderProperties('users'),
        ]);
    }

    public function store(UserStoreRequest $request, CreateNewUser $action): RedirectResponse
    {
        Gate::authorize('create', User::class);

        // Create the user
        $user = $action->create($request->safe()->all());

        // Notify the user
        toast(title: (string) $user->name, description: __('The user has been created.'));

        return redirect()->route('users.index');
    }

    public function update(User $user, UserUpdateRequest $request, UpdateUserProfileInformation $action): RedirectResponse
    {
        Gate::authorize('update', $user);

        // Update the user's profile information
        $action->update($user, $request->safe()->only(['name', 'email']));

        // Notify the user
        toast(title: (string) $user->name, description: __('The user has been updated.'));

        return back();
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        Gate::authorize('delete', $user);

        abort_if($request->user()?->is($user), 403, __('You cannot delete your own account.'));

        // Delete the user
        $user->deleteOrFail();

        // Notify the user
        toast(title: (string) $user->name, description: __('The user has been deleted.'), type: 'warning');

        return back();
    }
}

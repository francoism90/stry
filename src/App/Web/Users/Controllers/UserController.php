<?php

declare(strict_types=1);

namespace App\Web\Users\Controllers;

use App\Api\Users\Resources\UserResource;
use Domain\Users\Enums\UserScope;
use Domain\Users\Enums\UserSorter;
use Domain\Users\Filters\UserScopeFilter;
use Domain\Users\Models\User;
use Domain\Users\QueryBuilders\UserQueryBuilder;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
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
            ->query(fn (UserQueryBuilder $query) => $query->withCount('videos')->with('roles', 'permissions'))
            ->allowedFilters(
                AllowedFilter::custom('scope', new UserScopeFilter),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::oldest('oldest', 'created_at'),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 16);

        $scout->getCollection()->each(fn (User $user) => $user->append(['name', 'email', 'avatar']));

        return Inertia::render('Users/UserIndex', [
            'items' => Inertia::scroll(fn () => UserResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(UserScope::class),
            'sorters' => fn () => Options::forEnum(UserSorter::class),
            new ScoutBuilderProperties('users'),
        ]);
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        Gate::authorize('delete', $user);

        abort_if($request->user()?->is($user), 403, __('You cannot delete your own account.'));

        // Delete the user
        $user->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $user->name,
            'description' => __('The user has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

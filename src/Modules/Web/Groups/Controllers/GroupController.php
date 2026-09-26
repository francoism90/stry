<?php

declare(strict_types=1);

namespace Modules\Web\Groups\Controllers;

use Domain\Groups\Actions\UpdateGroupDetails;
use Domain\Groups\Enums\GroupScope;
use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Filters\GroupScopeFilter;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupProfileScope;
use Domain\Groups\Scopes\GroupTypeScope;
use Domain\Videos\Enums\VideoScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Filters\VideoScopeFilter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoGroupScope;
use Domain\Videos\Scopes\VideoProfileScope;
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
use Modules\Api\Groups\Requests\GroupStoreRequest;
use Modules\Api\Groups\Requests\GroupUpdateRequest;
use Modules\Api\Groups\Resources\GroupResource;
use Modules\Api\Videos\Resources\VideoResource;
use Modules\Web\Groups\Responses\GroupResourceProperty;
use Spatie\LaravelOptions\Options;
use Support\Scout\Sorts\RecommendedSorter;

class GroupController implements HasMiddleware
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
        Gate::authorize('viewAny', Group::class);

        // Scout builder
        $updatedSort = AllowedSort::field('updated', 'updated_at')->defaultDescending();

        $scout = ScoutBuilder::for(Group::class)
            ->tap(new GroupProfileScope)
            ->tap(new GroupTypeScope)
            ->query(function (Builder $query): void {
                $query->withCount('groupables');
            })
            ->allowedFilters(
                AllowedFilter::custom('scope', new GroupScopeFilter)->default(GroupScope::All->value),
            )
            ->allowedSorts(
                AllowedSort::field('name'),
                AllowedSort::field('videos', 'groupables')->defaultDescending(),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                $updatedSort,
            )
            ->defaultSort($updatedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Groups/GroupIndex', [
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(GroupScope::class),
            'sorters' => fn () => Options::forEnum(GroupSorter::class),
            new ScoutBuilderProperties('groups'),
        ]);
    }

    public function show(Group $group, Request $request): Response
    {
        Gate::authorize('view', $group);

        // Scout builder
        $recommendedSort = AllowedSort::custom('recommended', new RecommendedSorter);

        $scout = ScoutBuilder::for(Video::class)
            ->tap(new VideoGroupScope(group: $group, sort: $request->input('sort')))
            ->tap(new VideoProfileScope)
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('scope', new VideoScopeFilter),
            )
            ->allowedSorts(
                $recommendedSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'name'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort($recommendedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Groups/GroupView', [
            'group' => fn () => new GroupResourceProperty($group),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(VideoScope::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            new ScoutBuilderProperties('groups.videos'),
        ]);
    }

    public function store(GroupStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Group::class);

        // Create the group
        $group = $request->user()->findOrCreateGroup(
            name: $request->safe()->input('name'),
            type: GroupType::Custom,
            attributes: $request->safe()->only('content'),
        );

        // Notify the user
        toast(title: (string) $group->name, description: __('The group has been created.'));

        return redirect()->route('collections.show', $group);
    }

    public function update(Group $group, GroupUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $group);

        // Update group details
        app(UpdateGroupDetails::class)->handle(
            group: $group,
            attributes: $request->safe()->all()
        );

        // Notify the user
        toast(title: (string) $group->name, description: __('The group has been updated.'));

        return back();
    }

    public function destroy(Group $group): RedirectResponse
    {
        Gate::authorize('delete', $group);

        // Delete the group
        $group->deleteOrFail();

        // Notify the user
        toast(title: (string) $group->name, description: __('The group has been deleted.'), type: 'warning');

        return back();
    }
}

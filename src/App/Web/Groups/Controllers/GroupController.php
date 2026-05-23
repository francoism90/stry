<?php

declare(strict_types=1);

namespace App\Web\Groups\Controllers;

use App\Api\Groups\Requests\GroupStoreRequest;
use App\Api\Groups\Requests\GroupUpdateRequest;
use App\Api\Groups\Resources\GroupResource;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Groups\Responses\GroupResourceProperty;
use Domain\Groups\Actions\UpdateGroupDetails;
use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Domain\Groups\Scopes\GroupProfileScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoGroupScope;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Controllers\Controller;
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
use Support\Scout\Filters\FiltersUnseen;
use Support\Scout\Sorts\RecommendedSorter;

class GroupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Group::class);

        // Scout builder
        $updatedSort = AllowedSort::field('updated', 'updated_at')->defaultDescending();

        $scout = ScoutBuilder::for(Group::class)
            ->tap(new GroupProfileScope)
            ->query(fn (GroupQueryBuilder $query) => $query->withCount('groupables'))
            ->allowedFilters(
                AllowedFilter::exact('type'),
            )
            ->allowedSorts(
                AllowedSort::field('name'),
                AllowedSort::field('videos', 'groupables')->defaultDescending(),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                $updatedSort,
            )
            ->defaultSort($updatedSort)
            ->jsonSimplePaginate(defaultSize: 20);

        return Inertia::render('App/Groups/GroupIndex', [
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'sort' => fn () => $request->input('sort'),
            'type' => fn () => $request->input('type'),
            'sorters' => fn () => Options::forEnum(GroupSorter::class),
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
                AllowedFilter::custom('unseen', new FiltersUnseen),
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
            ->jsonSimplePaginate(defaultSize: 20);

        return Inertia::render('App/Groups/GroupView', [
            'group' => fn () => new GroupResourceProperty($group),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            'filters' => fn () => $request->input('filter', []),
            'sort' => fn () => $request->input('sort'),
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
        Inertia::flash([
            'title' => (string) $group->name,
            'description' => __('The group has been created.'),
            'type' => 'success',
        ]);

        return redirect()->route('collections.show', $group);
    }

    public function edit(Group $group): Response
    {
        Gate::authorize('update', $group);

        return Inertia::render('App/Groups/GroupEdit', [
            'group' => fn () => new GroupResourceProperty($group),
        ]);
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
        Inertia::flash([
            'title' => (string) $group->name,
            'description' => __('The group has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Group $group): RedirectResponse
    {
        Gate::authorize('delete', $group);

        // Delete the group
        $group->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $group->name,
            'description' => __('The group has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

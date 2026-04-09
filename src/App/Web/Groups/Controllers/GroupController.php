<?php

declare(strict_types=1);

namespace App\Web\Groups\Controllers;

use App\Api\Groups\Requests\GroupIndexRequest;
use App\Api\Groups\Requests\GroupStoreRequest;
use App\Api\Groups\Requests\GroupUpdateRequest;
use App\Api\Groups\Resources\GroupResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Groups\Responses\GroupResourceProperty;
use Domain\Groups\Actions\UpdateGroupDetails;
use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupFilterScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

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

    public function index(GroupIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Group::class);

        // Apply filters
        $sort = $request->safe()->input('sort');

        // Scout builder
        $scout = Group::search()
            ->tap(new GroupFilterScope(sort: $sort))
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Groups/GroupIndex', [
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'sort' => fn () => $sort,
            'sorters' => fn () => Options::forEnum(GroupSorter::class),
        ]);
    }

    public function show(Group $group, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $group);

        // Apply filters
        $sort = $request->safe()->input('sort');

        // Scout builder
        $scout = Video::search()
            ->tap(new VideoFilterScope(
                group: $group,
                sort: $sort,
            ))
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Groups/GroupView', [
            'group' => fn () => new GroupResourceProperty($group),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'sort' => fn () => $sort,
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
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

<?php

declare(strict_types=1);

namespace App\Web\Groups\Controllers;

use App\Api\Groups\Requests\GroupIndexRequest;
use App\Api\Groups\Requests\GroupStoreRequest;
use App\Api\Groups\Requests\GroupUpdateRequest;
use App\Api\Groups\Resources\GroupResource;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Groups\Responses\GroupResourceProperty;
use Domain\Groups\Actions\UpdateGroupDetails;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(GroupIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Group::class);

        // Apply filters
        $type = $request->safe()->input('type');

        // Scout builder
        $scout = Group::search()
            ->tap(new GroupFilterScope(type: $type))
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Groups/GroupIndex', [
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'type' => fn () => $type,
            'types' => fn () => GroupType::options(),
        ]);
    }

    public function show(Group $group): Response
    {
        Gate::authorize('view', $group);

        // Paginate the group's videos
        $videos = $group->videos()
            ->with('media', 'tags')
            ->simplePaginate(perPage: 18);

        return Inertia::render('App/Groups/GroupView', [
            'group' => fn () => new GroupResourceProperty($group),
            'items' => Inertia::scroll(fn () => VideoResource::collection($videos)),
        ]);
    }

    public function store(GroupStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Group::class);

        // Create the group
        $group = Group::create($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $group->name,
            'description' => __('The group has been created.'),
        ]);

        return back();
    }

    public function edit(Group $group): Response
    {
        Gate::authorize('update', $group);

        return Inertia::render('App/Groups/GroupEdit', [
            'group' => fn () => new GroupResourceProperty($group),
            'types' => fn () => GroupType::options(),
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
        ]);

        return back();
    }
}

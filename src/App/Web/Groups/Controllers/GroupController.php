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
use Domain\Groups\Enums\GroupOrder;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupFilterScope;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
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
        $order = $request->safe()->input('order');

        // Scout builder
        $scout = Group::search()
            ->tap(new GroupFilterScope(order: $order))
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Groups/GroupIndex', [
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'order' => fn () => $request->safe()->input('order', 'recommended'),
            'orders' => fn () => GroupOrder::options(),
        ]);
    }

    public function show(Group $group, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $group);

        // Scout builder
        $scout = Video::search()
            ->tap(new VideoFilterScope(
                group: $group,
                order: $request->safe()->input('order'),
            ))
            ->simplePaginate(perPage: 18);

        return Inertia::render('App/Groups/GroupView', [
            'group' => fn () => new GroupResourceProperty($group),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'order' => fn () => $request->safe()->input('order', 'recommended'),
            'orders' => fn () => VideoOrder::options(),
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

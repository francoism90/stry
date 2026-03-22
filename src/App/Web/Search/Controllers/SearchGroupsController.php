<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Enums\GroupOrder;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchGroupsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, string $query = ''): Response
    {
        Gate::authorize('viewAny', Group::class);

        $order = $request->input('order');

        $scout = Group::search($query)
            ->tap(new GroupFilterScope(order: $order))
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Search/SearchCollections', [
            'search' => fn () => $query,
            'order' => fn () => $order,
            'orders' => fn () => GroupOrder::options(),
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
        ]);
    }
}

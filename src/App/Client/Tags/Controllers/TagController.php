<?php

declare(strict_types=1);

namespace App\Client\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagTypeScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(TagIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Apply filters
        $search = $request->safe()->input('search');
        $type = $request->safe()->input('type', TagType::Genre);

        // Scout builder
        $scout = Tag::search($search)
            ->tap(new TagTypeScope(type: $type))
            ->paginate(perPage: 24);

        return Inertia::render('Client/Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
            'types' => fn () => TagType::options(),
            'search' => fn () => $search,
            'type' => fn () => $type,
        ]);
    }
}

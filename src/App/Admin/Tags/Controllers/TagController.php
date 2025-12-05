<?php

declare(strict_types=1);

namespace App\Admin\Tags\Controllers;

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
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Apply filters
        $type = $request->safe()->input('type', TagType::Genre);

        // Scout builder
        $scout = Tag::search($request->safe()->input('search'))
            ->tap(new TagTypeScope($type))
            ->simplePaginate(18)
            ->through(fn (Tag $tag) => new TagResource($tag));

        return Inertia::render('Admin/Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'type' => fn () => $type,
            'types' => fn () => TagType::options(),
        ]);
    }
}

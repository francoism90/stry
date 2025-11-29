<?php

declare(strict_types=1);

namespace App\Admin\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Tags\Responses\TagEditProperties;
use App\Web\Tags\Responses\TagViewProperties;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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

    public function index(TagIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        $scout = Tag::search($request->safe()->input('search'))
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos'))
            // ->tap(new VideoFilterScope($request))
            ->simplePaginate(18);

        return Inertia::render('Admin/TagIndex', [
            'search' => fn () => $request->safe()->input('search', ''),
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
        ]);
    }
}

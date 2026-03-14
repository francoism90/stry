<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Web\Search\Responses\GroupSearchProperty;
use App\Web\Search\Responses\TagSearchProperty;
use App\Web\Search\Responses\VideoSearchProperty;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(string $query = ''): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('App/Search/SearchIndex', [
            'search' => fn () => $query,
            'videos' => fn () => new VideoSearchProperty($query),
            'tags' => fn () => new TagSearchProperty($query),
            'collections' => fn () => new GroupSearchProperty($query),
        ]);
    }
}

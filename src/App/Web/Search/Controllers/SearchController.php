<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Web\Search\Responses\GroupSearchProperty;
use App\Web\Search\Responses\TagSearchProperty;
use App\Web\Search\Responses\VideoSearchProperty;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchController implements HasMiddleware
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
        Gate::authorize('viewAny', Video::class);

        if ($query !== '') {
            $request->session()->cache()->put('search', $query, now()->addHour());
        }

        return Inertia::render('Search/SearchIndex', [
            'search' => fn () => $query,
            'videos' => fn () => new VideoSearchProperty($query),
            'tags' => fn () => new TagSearchProperty($query),
            'collections' => fn () => new GroupSearchProperty($query),
        ]);
    }
}

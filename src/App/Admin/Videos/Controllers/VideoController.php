<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Responses\VideoEditProperties;
use App\Web\Videos\Responses\VideoPlaylistProperty;
use App\Web\Videos\Responses\VideoQueueProperty;
use App\Web\Videos\Responses\VideoViewProperties;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        $scout = Video::search($request->safe()->input('search'))
            // ->tap(new VideoFilterScope($request))
            ->paginate(12);

        return Inertia::render('Admin/VideoIndex', [
            'search' => fn () => $request->safe()->input('search', ''),
            'items' => fn () => VideoResource::collection($scout),
        ]);
    }
}

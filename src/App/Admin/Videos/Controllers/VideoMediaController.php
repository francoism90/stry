<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Resources\MediaResource;
use App\Api\Videos\Resources\VideoResource;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoMediaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(MediaIndexRequest $request, Video $video): Response
    {
        Gate::authorize('viewAny', Media::class);

        $media = $video->media()->simplePaginate(16);

        return Inertia::render('Admin/Videos/Media/Index', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'items' => Inertia::scroll(fn () => MediaResource::collection($media)),
        ]);
    }

    public function create(Video $video): Response
    {
        Gate::authorize('create', Media::class);

        return Inertia::render('Admin/Videos/Media/Create', [
            'video' => $video,
        ]);
    }

    public function store(MediaIndexRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('create', Media::class);

        $video->media()->create($request->safe()->all());

        return redirect()->route('admin.videos.media.index', $video);
    }
}

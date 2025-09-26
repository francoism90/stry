<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Resources\MediaResource;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Media\Responses\MediaQueryCollection;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoMediaController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Video $video, MediaIndexRequest $request): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoMedia', [
            'video' => fn () => $video->toResource(VideoResource::class),
            'items' => Inertia::scroll(fn () => MediaResource::collection($video->media()->simplePaginate(24))),
        ]);
    }

    public function store(Request $request): Response
    {
        abort(404);
    }

    public function create(): Response
    {
        abort(404);
    }

    public function show(Video $video, Media $media): Response
    {
        Gate::authorize('update', [$video, $media]);

        abort(404);
    }

    public function edit(Video $video, Media $media): Response
    {
        Gate::authorize('update', [$video, $media]);

        abort(404);
    }

    public function update(Request $request, Video $video, Media $media): Response
    {
        Gate::authorize('update', [$video, $media]);

        abort(404);
    }

    public function destroy(Video $video, Media $media): Response
    {
        Gate::authorize('delete', [$video, $media]);

        abort(404);
    }
}

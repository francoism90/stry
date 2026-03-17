<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Resources\MediaResource;
use App\Web\Videos\Responses\VideoResourceProperty;
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
            new Middleware('precognitive'),
        ];
    }

    public function index(MediaIndexRequest $request, Video $video): Response
    {
        Gate::authorize('viewAny', Media::class);

        // Fetch media for the video
        $media = $video
            ->media()
            ->simplePaginate(perPage: 16)
            ->through(fn ($item) => $item->append(['custom_properties', 'generated_conversions']));

        return Inertia::render('App/Videos/Media/MediaIndex', [
            'video' => fn () => new VideoResourceProperty($video, ['filesize']),
            'items' => Inertia::scroll(fn () => MediaResource::collection($media)),
        ]);
    }

    public function create(Video $video): Response
    {
        Gate::authorize('create', Media::class);

        return Inertia::render('App/Videos/Media/MediaCreate', [
            'video' => fn () => new VideoResourceProperty($video),
        ]);
    }

    public function store(MediaIndexRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('create', Media::class);

        // Create new media for the video
        $media = $video->media()->create($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $media->name,
            'description' => __('The media has been added to the video.'),
        ]);

        return back();
    }
}

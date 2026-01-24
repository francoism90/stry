<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Media\Responses\MediaResourceProperty;
use App\Admin\Media\Responses\TranscodeResourceProperty;
use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Requests\MediaUpdateRequest;
use App\Api\Media\Resources\MediaResource;
use Domain\Media\Actions\UpdateMediaDetails;
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

        // Fetch media for the video
        $media = $video->media()
            ->simplePaginate(16)
            ->through(fn ($item) => $item->append(['custom_properties', 'generated_conversions']));

        return Inertia::render('Admin/Videos/Media/MediaIndex', [
            'video' => fn () => new VideoResourceProperty($video),
            'items' => Inertia::scroll(fn () => MediaResource::collection($media)),
        ]);
    }

    public function create(Video $video): Response
    {
        Gate::authorize('create', Media::class);

        return Inertia::render('Admin/Videos/Media/Create', [
            'video' => fn () => new VideoResourceProperty($video),
        ]);
    }

    public function store(MediaIndexRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('create', Media::class);

        $video->media()->create($request->safe()->all());

        return redirect()->route('admin.videos.media.index', $video);
    }

    public function show(Video $video, Media $media): Response
    {
        Gate::authorize('view', $media);

        return Inertia::render('Admin/Videos/Media/Show', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'media' => fn () => new MediaResourceProperty(media: $media),
        ]);
    }

    public function edit(Video $video, Media $media): Response
    {
        Gate::authorize('update', $media);

        return Inertia::render('Admin/Videos/Media/MediaEdit', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'media' => fn () => new MediaResourceProperty(media: $media),
            'transcodes' => fn () => new TranscodeResourceProperty(media: $media),
        ]);
    }

    public function update(MediaUpdateRequest $request, Video $video, Media $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        app(UpdateMediaDetails::class)->handle($media, $request->safe()->all());

        return back();
    }

    public function destroy(Video $video, Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        $media->deleteOrFail();

        return redirect()->route('admin.videos.media.index', $video);
    }
}

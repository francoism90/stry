<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Playlists\Resources\PlaylistResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Scopes\VideoListScope;
use Domain\Videos\Actions\CreateVideoPlaylist;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Actions\MarkVideoAsDeleted;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $items = Video::query()
            ->tap(new VideoListScope)
            ->cursorPaginate(perPage: 24, cursor: (string) $request->input('page', ''))
            ->through(fn (Video $video) => VideoResource::make($video));

        return Inertia::render('Videos/VideoIndex', [
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function create()
    {
        // Gate::authorize('create', Video::class);

        // return Inertia::render('Videos/VideoCreate', [
        //     //
        // ]);
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        // Make sure the video has a playlist
        app(CreateVideoPlaylist::class)->handle($video);

        return Inertia::render('Videos/VideoView', [
            'video' => fn () => $video->append(['content', 'titles'])->toResource(VideoResource::class),
            'playlist' => fn () => $video->getFirstPlaylist('clips')?->toResource(PlaylistResource::class),
            'queue' => Inertia::defer(fn () => app(GetSimilarVideos::class)->handle($video), 'items'),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            'video' => fn () => $video->load('tags')->append(['content', 'titles'])->toResource(VideoResource::class),
        ]);
    }

    public function update(VideoUpdateRequest $request, Video $video): RedirectResponse
    {
        app(UpdateVideoDetails::class)->handle($video, $request->validated());

        flash()->success('Video updated successfully!');

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        app(MarkVideoAsDeleted::class)->handle($video);

        return redirect()->route('videos.index');
    }
}

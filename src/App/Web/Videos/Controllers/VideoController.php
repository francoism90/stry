<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Responses\VideoGroupsProperty;
use App\Web\Videos\Responses\VideoMediaProperty;
use App\Web\Videos\Responses\VideoPlaylistProperty;
use App\Web\Videos\Responses\VideoPlaylistsProperty;
use App\Web\Videos\Responses\VideoProgressProperty;
use App\Web\Videos\Responses\VideoQueueProperty;
use App\Web\Videos\Responses\VideoResourceProperty;
use App\Web\Videos\Responses\VideoTranscodesProperty;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Enums\VideoLibraryScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Filters\VideoLibraryScopeFilter;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;
use Support\Scout\Filters;
use Support\Scout\Sorts\RecommendedSorter;

class VideoController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Relevant sort options
        $defaultSort = AllowedSort::custom('recommended', new RecommendedSorter);

        // Scout builder
        $scout = ScoutBuilder::for(Video::class)
            ->tap(new VideoProfileScope)
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('scope', new VideoLibraryScopeFilter),
                AllowedFilter::custom('tagged', new Filters\FilterTagged),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'title'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 16);

        $scout->getCollection()->each(fn (Video $video) => $video->append(['filesize', 'codec']));

        return Inertia::render('Videos/VideoLibrary', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(VideoLibraryScope::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            new ScoutBuilderProperties('videos'),
        ]);
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        // Dispatch the job to create a playlist if necessary
        PlaylistVideo::dispatchIf(
            ! $video->hasPlaylist(),
            $video,
        );

        return Inertia::render('Videos/VideoView', [
            'video' => fn () => new VideoResourceProperty(video: $video, appends: ['titles', 'summary', 'snapshot']),
            'playlist' => fn () => new VideoPlaylistProperty(video: $video),
            'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
            'groups' => Inertia::defer(fn () => new VideoGroupsProperty($video, Auth::user())),
            'media' => Inertia::defer(fn () => new VideoMediaProperty($video)),
            'playlists' => Inertia::defer(fn () => new VideoPlaylistsProperty($video)),
            'transcodes' => Inertia::defer(fn () => new VideoTranscodesProperty($video)),
            'queue' => Inertia::defer(fn () => new VideoQueueProperty($video))->deepMerge()->matchOn('data.id'),
            new ScoutBuilderProperties('videos'),
        ]);
    }

    public function update(Video $video, VideoUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        // Update video details
        app(UpdateVideoDetails::class)->handle(
            video: $video,
            attributes: $request->safe()->all()
        );

        // Notify the user
        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('The video has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        // Delete the video
        $video->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('The video has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Videos\Controllers;

use App\Modules\Videos\Enums\VideoFilter;
use App\Modules\Videos\Enums\VideoSorter;
use App\Modules\Videos\Requests\VideoUpdateRequest;
use App\Modules\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Foundation\Http\Properties\LayoutProperties;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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

    public function index(ScoutBuilderProperties $properties): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Relevant sort options
        $recommendedSort = AllowedSort::custom('recommended', new RecommendedSorter);

        // Scout builder
        $scout = ScoutBuilder::for(Video::class)
            // ->tap(new VideoProfileScope)
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('shorts', new Filters\FilterShorts),
                AllowedFilter::custom('tagged', new Filters\FilterTagged),
                AllowedFilter::custom('untagged', new Filters\FilterUntagged),
                AllowedFilter::custom('unseen', new Filters\FilterUnseen),
            )
            ->allowedSorts(
                $recommendedSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'title'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort($recommendedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Resources/ResourceIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'filters' => fn () => Options::forEnum(VideoFilter::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            'layout' => fn () => new LayoutProperties(
                title: __('Videos'),
                description: __('Browse and manage your videos.'),
                id: 'videos.index',
                type: 'video',
            ),
            $properties,
        ]);
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        // Dispatch the job to create a playlist if necessary
        // PlaylistVideo::dispatchIf(
        //     ! $video->hasPlaylist(),
        //     $video,
        // );

        return Inertia::render('Videos/VideoView', [
            // 'video' => fn () => new VideoResourceProperty(video: $video),
            // 'playlist' => fn () => new VideoPlaylistProperty(video: $video),
            // 'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
            // 'groups' => Inertia::defer(fn () => new VideoGroupsProperty($video, Auth::user())),
            // 'queue' => Inertia::defer(fn () => new VideoQueueProperty($video))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Video::class);

        return Inertia::render('Videos/VideoCreate', [
            // 'locales' => fn () => UserLocale::options(),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        // Define the attributes to append to the video resource
        $appends = [
            'titles',
            'content',
            'summary',
            'snapshot',
            'filesize',
        ];

        return Inertia::render('Videos/VideoEdit', [
            // 'video' => fn () => new VideoResourceProperty($video, $appends),
            // 'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
            // 'locales' => fn () => UserLocale::options(),
        ]);
    }

    public function update(Video $video, VideoUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        // Update video details
        // app(UpdateVideoDetails::class)->handle(
        //     video: $video,
        //     attributes: $request->safe()->all()
        // );

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

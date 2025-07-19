<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Playlists\Resources\PlaylistCollection;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Algos\GenerateVideoRecommendation;
use Domain\Videos\Jobs\TranscodeVideo;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function index(Request $request)
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

        // TODO: remove this debugging line
        TranscodeVideo::dispatch($video);

        return Inertia::render('Videos/VideoView', [
            'item' => fn () => VideoResource::make($video->append(['content', 'titles'])),
            'playlists' => fn () => PlaylistCollection::make($video->playlists),
            'queue' => Inertia::defer(fn () => GenerateVideoRecommendation::make(), 'sections'),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            'item' => fn () => VideoResource::make($video->append(['content', 'titles'])),
            'playlists' => fn () => PlaylistCollection::make($video->playlists),
        ]);
    }
}

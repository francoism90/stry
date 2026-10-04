<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Chapters\Actions\GenerateChapterVtt;
use Domain\Videos\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoChaptersVttController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    /**
     * The chapters as WebVTT for the player's chapter track, built from the current chapters, so
     * edits show up straight away.
     */
    public function __invoke(Video $video, GenerateChapterVtt $vtt): Response
    {
        Gate::authorize('view', $video);

        abort_if($video->chapters->isEmpty(), 404);

        return response($vtt->handle($video), 200, [
            'Content-Type' => 'text/vtt; charset=utf-8',
            'Cache-Control' => 'private, no-cache',
        ]);
    }
}

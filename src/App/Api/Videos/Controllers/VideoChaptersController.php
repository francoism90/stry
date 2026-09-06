<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Chapters\Actions\GenerateChapterVtt;
use Domain\Videos\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoChaptersController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('throttle:vod'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Video $video, GenerateChapterVtt $generateChapterVtt): Response
    {
        Gate::authorize('view', $video);

        return response($generateChapterVtt->handle($video->loadMissing('chapters')), 200, [
            'Content-Type' => 'text/vtt; charset=utf-8',
            'Cache-Control' => 'private, max-age=60, must-revalidate',
        ]);
    }
}

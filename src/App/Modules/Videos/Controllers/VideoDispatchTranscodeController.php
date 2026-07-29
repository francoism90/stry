<?php

declare(strict_types=1);

namespace App\Modules\Videos\Controllers;

use Domain\Videos\Jobs\TranscodeVideo;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoDispatchTranscodeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);

        TranscodeVideo::dispatchIf(
            ! $video->hasTranscode(),
            $video
        );

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('Queued for transcoding.'),
            'type' => 'info',
        ]);

        return back();
    }
}

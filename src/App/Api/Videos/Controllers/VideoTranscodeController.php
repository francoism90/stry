<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Videos\Jobs\TranscodeVideo;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoTranscodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(Video $video, Request $request): RedirectResponse|Response
    {
        Gate::authorize('update', $video);

        // Perform the transcode action
        TranscodeVideo::dispatchIf(
            ! $video->hasTranscode(),
            $video
        );

        if ($request->inertia()) {
            // Notify the user
            Inertia::flash([
                'title' => (string) $video->name,
                'description' => __('Queued for transcoding.'),
            ]);

            return back();
        }

        return response()->noContent();
    }
}

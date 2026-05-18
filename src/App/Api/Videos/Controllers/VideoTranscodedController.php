<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Transcodes\Actions\ImportTranscode;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

use function Illuminate\Support\defer;

class VideoTranscodedController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Video $video, Request $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        // Get all completed transcodes for the video and queue them for import.
        $items = $video->transcodes()
            ->completed()
            ->get();

        defer(function () use ($items): void {
            $items->each(fn (Transcode $transcode) => app(ImportTranscode::class)->handle($transcode));
        });

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('Queued for import.'),
            'type' => 'info',
        ]);

        return back();
    }
}

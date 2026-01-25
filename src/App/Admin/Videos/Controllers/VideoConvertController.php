<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use Domain\Media\Actions\CreateMediaTranscode;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoConvertController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);

        // For simplicity, we will transcode the first clip only.
        $media = $video->getClipCollection()->first();

        if (! $media instanceof Media) {
            abort(404, 'No media found for video.');
        }

        // Create transcode job
        app(CreateMediaTranscode::class)->handle($media);

        // Flash message
        Inertia::flash('message', __('Video conversion started successfully.'));

        return back();
    }
}

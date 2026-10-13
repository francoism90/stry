<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Videos\Jobs\ExportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoExportController implements HasMiddleware
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

        ExportVideo::dispatch($video);

        toast(title: (string) $video->name, description: __('Queued for export.'), type: 'info');

        return back();
    }
}

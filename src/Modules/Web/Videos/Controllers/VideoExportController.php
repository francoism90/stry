<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\ExportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Api\Videos\Requests\VideoExportRequest;

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

    public function __invoke(VideoExportRequest $request, Video $video, #[CurrentUser] User $user): RedirectResponse
    {
        Gate::authorize('update', $video);

        $deleteAfterExport = $request->shouldDeleteAfterExport();

        if ($deleteAfterExport) {
            Gate::authorize('delete', $video);
        }

        ExportVideo::dispatch($video, $user, $deleteAfterExport);

        toast(title: (string) $video->name, description: __('Queued for export.'), type: 'info');

        return back();
    }
}

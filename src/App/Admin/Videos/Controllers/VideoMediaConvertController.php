<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use Domain\Media\Jobs\ConvertMediaJob;
use Domain\Media\Models\Media;
use Domain\Media\Models\Transcode;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoMediaConvertController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Video $video, Media $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        $transcode = Transcode::create([
            'video_id' => $video->id,
            'media_id' => $media->id,
            'codec' => 'av1',
            'state' => 'pending',
        ]);

        ConvertMediaJob::dispatch($transcode);

        return back();
    }
}

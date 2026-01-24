<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use Domain\Videos\Actions\ImportVideoTranscodes;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoTranscodedController extends Controller implements HasMiddleware
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

        app(ImportVideoTranscodes::class)->handle($video);

        return back();
    }
}

<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use Domain\Media\Actions\ReplaceMediaWithTranscode;
use Domain\Media\Models\Media;
use Domain\Media\Models\Transcode;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class MediaTranscodeReplaceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Media $media, Transcode $transcode): RedirectResponse
    {
        Gate::authorize('update', $media);

        app(ReplaceMediaWithTranscode::class)->handle($transcode);

        return back();
    }
}

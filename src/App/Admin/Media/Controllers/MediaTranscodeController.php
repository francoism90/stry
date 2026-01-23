<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use Domain\Media\Actions\CreateMediaTranscode;
use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MediaTranscodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Media $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        abort_unless(
            Str::startsWith($media->mime_type, 'video/'),
            422,
            'Media must be a video to convert.'
        );

        app(CreateMediaTranscode::class)->handle($media);

        return back();
    }
}

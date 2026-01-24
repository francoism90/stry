<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use Domain\Media\Actions\AddMediaFromTranscode;
use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class MediaConvertedController extends Controller implements HasMiddleware
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

        app(AddMediaFromTranscode::class)->handle($media);

        return back();
    }
}

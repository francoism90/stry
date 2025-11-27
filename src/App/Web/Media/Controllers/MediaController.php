<?php

declare(strict_types=1);

namespace App\Web\Media\Controllers;

use App\Api\Media\Requests\MediaIndexRequest;
use App\Web\Media\Responses\MediaCollection;
use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(MediaIndexRequest $request, MediaCollection $items): Response
    {
        Gate::authorize('viewAny', Media::class);

        return Inertia::render('Media/MediaIndex', $items);
    }
}

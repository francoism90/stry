<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Media\Resources\MediaResource;
use App\Web\Videos\Responses\VideoResourceProperty;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoMediaController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Video $video): Response
    {
        Gate::authorize('viewAny', Media::class);

        // Fetch media for the video
        $media = $video
            ->media()
            ->latest()
            ->simplePaginate(perPage: 16)
            ->through(fn ($item) => $item->append(['custom_properties', 'generated_conversions']));

        return Inertia::render('App/Videos/Media/MediaIndex', [
            'video' => fn () => new VideoResourceProperty($video, ['filesize']),
            'items' => Inertia::scroll(fn () => MediaResource::collection($media)),
        ]);
    }
}

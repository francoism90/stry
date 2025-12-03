<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Resources\MediaResource;
use Domain\Media\Enums\MediaOrder;
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

    public function index(MediaIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Media::class);

        // Apply filters
        $sort = $request->safe()->input('sort', MediaOrder::Newest);

        // Scout builder
        $scout = Media::search($request->safe()->input('search'))
            ->simplePaginate(12)
            ->through(fn (Media $media) => new MediaResource($media));

        return Inertia::render('Admin/Media/MediaIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $sort,
            'sorters' => fn () => MediaOrder::options(),
        ]);
    }
}

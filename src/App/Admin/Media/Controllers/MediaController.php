<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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

        $media = Media::query()->simplePaginate(16);

        return Inertia::render('Admin/Media/MediaIndex', [
            'items' => Inertia::scroll(fn () => MediaResource::collection($media)),
        ]);
    }

    public function edit(Media $media): Response
    {
        Gate::authorize('update', $media);

        return Inertia::render('Admin/Media/Edit', [
            'media' => $media,
        ]);
    }

    public function update(MediaIndexRequest $request, Media $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        $media->update($request->safe()->all());

        return redirect()->route('admin.media.index');
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        $media->delete();

        return redirect()->route('admin.media.index');
    }
}

<?php

declare(strict_types=1);

namespace App\Admin\Media\Controllers;

use App\Admin\Media\Responses\MediaResourceProperty;
use App\Api\Media\Requests\MediaIndexRequest;
use App\Api\Media\Requests\MediaUpdateRequest;
use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Domain\Media\Scopes\MediaFilterScope;
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
            new Middleware('precognitive'),
        ];
    }

    public function index(MediaIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Media::class);

        // Query builder
        $query = Media::query()
            ->tap(new MediaFilterScope)
            ->simplePaginate(16);

        return Inertia::render('Admin/Media/MediaIndex', [
            'items' => Inertia::scroll(fn () => MediaResource::collection($query)),
        ]);
    }

    public function edit(Media $media): Response
    {
        Gate::authorize('update', $media);

        return Inertia::render('Admin/Media/MediaEdit', [
            'media' => fn () => new MediaResourceProperty($media),
        ]);
    }

    public function update(Media $media, MediaUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $media);

        // Update media details
        $media->updateOrFail($request->safe()->all());

        // Flash message
        return Inertia::flash('message', __('The media has been updated.'))->back();
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        // Delete the media
        $media->deleteOrFail();

        return Inertia::flash('message', __('The media has been deleted.'))->back();
    }
}

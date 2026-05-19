<?php

declare(strict_types=1);

namespace App\Web\Media\Controllers;

use App\Api\Media\Requests\MediaUpdateRequest;
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
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(Media $media): Response
    {
        Gate::authorize('update', $media);

        return Inertia::render('App/Media/MediaShow', [
            'media' => fn () => new MediaResource($media->append(['custom_properties', 'generated_conversions'])),
        ]);
    }

    public function update(Media $media, MediaUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $media);

        // Update media details
        $media->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $media->name,
            'description' => __('The media has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        // Delete the media
        $media->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $media->name,
            'description' => __('The media has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

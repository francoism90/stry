<?php

declare(strict_types=1);

namespace Modules\Web\Media\Controllers;

use Modules\Api\Media\Requests\MediaUpdateRequest;
use Domain\Media\Actions\UpdateMediaDetails;
use Domain\Media\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class MediaController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function update(Media $media, MediaUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $media);

        // Update media details
        app(UpdateMediaDetails::class)->handle(
            media: $media,
            attributes: $request->safe()->all(),
        );

        // Notify the user
        toast(title: (string) $media->name, description: __('The media has been updated.'));

        return back();
    }

    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        // Delete the media
        $media->deleteOrFail();

        // Notify the user
        toast(title: (string) $media->name, description: __('The media has been deleted.'), type: 'warning');

        return back();
    }
}

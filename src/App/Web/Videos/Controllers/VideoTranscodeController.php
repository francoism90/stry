<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoTranscodeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function update(TranscodeUpdateRequest $request, Video $video, Transcode $transcode): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        // Update the transcode with validated data
        $transcode->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Video $video, Transcode $transcode): RedirectResponse
    {
        Gate::authorize('delete', $transcode);

        // Delete the transcode
        $transcode->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

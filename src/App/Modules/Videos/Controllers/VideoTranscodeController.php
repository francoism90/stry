<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Modules\Transcodes\Requests\TranscodeUpdateRequest;
use App\Modules\Transcodes\Resources\TranscodeResource;
use App\Web\Videos\Responses\VideoResourceProperty;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoTranscodeController extends Controller implements HasMiddleware
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
        Gate::authorize('viewAny', Transcode::class);

        // Fetch transcodes for the video
        $transcodes = $video
            ->transcodes()
            ->latest()
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Videos/Transcodes/TranscodeIndex', [
            'video' => fn () => new VideoResourceProperty($video, ['filesize']),
            'items' => Inertia::scroll(fn () => TranscodeResource::collection($transcodes)),
        ]);
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

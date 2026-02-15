<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Transcodes\Responses\TranscodeResourceProperty;
use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Transcodes\Requests\TranscodeIndexRequest;
use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use App\Api\Transcodes\Resources\TranscodeResource;
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
            new Middleware('precognitive'),
        ];
    }

    public function index(TranscodeIndexRequest $request, Video $video): Response
    {
        Gate::authorize('viewAny', Transcode::class);

        // Fetch transcodes for the video
        $transcodes = $video->transcodes()->simplePaginate(16);

        return Inertia::render('Admin/Videos/Transcodes/TranscodeIndex', [
            'video' => fn () => new VideoResourceProperty($video),
            'items' => Inertia::scroll(fn () => TranscodeResource::collection($transcodes)),
        ]);
    }

    public function edit(Video $video, Transcode $transcode): Response
    {
        Gate::authorize('update', $transcode);

        return Inertia::render('Admin/Videos/Transcodes/TranscodeEdit', [
            'video' => fn () => new VideoResourceProperty($video),
            'transcode' => fn () => new TranscodeResourceProperty($transcode),
        ]);
    }

    public function update(TranscodeUpdateRequest $request, Video $video, Transcode $transcode): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        // Update the transcode with validated data
        $transcode->updateOrFail($request->safe()->all());

        // Flash message
        Inertia::flash('message', __('Transcode updated successfully.'));

        return back();
    }

    public function destroy(Video $video, Transcode $transcode): RedirectResponse
    {
        Gate::authorize('delete', $transcode);

        // Delete the transcode
        $transcode->deleteOrFail();

        // Flash message
        Inertia::flash('message', __('Transcode deleted successfully.'));

        return redirect()->route('admin.videos.transcodes.index', $video);
    }
}

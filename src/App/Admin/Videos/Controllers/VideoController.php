<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Videos\Responses\VideoProgressProperty;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Apply filters
        $search = $request->safe()->input('search');
        $order = $request->safe()->input('order', VideoOrder::Default);

        // Scout builder
        $scout = Video::search($search)
            ->tap(new VideoFilterScope(order: $order))
            ->paginate(perPage: 16)
            ->through(fn ($video) => $video->append('timestamp', 'filesize'));

        return Inertia::render('Admin/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'search' => fn () => $search,
            'order' => fn () => $order,
            'orders' => fn () => VideoOrder::options(),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Admin/Videos/VideoEdit', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
        ]);
    }

    public function update(Video $video, VideoUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        // Update video details
        app(UpdateVideoDetails::class)->handle(
            video: $video,
            attributes: $request->safe()->all()
        );

        // Flash message
        Inertia::flash('message', __('The video has been updated.'));

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        // Delete the video
        $video->deleteOrFail();

        // Flash message
        Inertia::flash('message', __('The video has been deleted.'));

        return redirect()->route('admin.videos.index');
    }
}

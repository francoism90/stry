<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(): Response
    {
        Gate::authorize('viewAny', Video::class);

        return response()->noContent();
    }

    public function store(Request $request): Response
    {
        Gate::authorize('create', Video::class);

        return response()->noContent();
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        return response()->noContent();
    }

    public function update(VideoUpdateRequest $request, Video $video): VideoResource
    {
        Gate::authorize('update', $video);

        $video = app(UpdateVideoDetails::class)->handle($video, $request->safe()->all());

        return VideoResource::make($video);
    }

    public function destroy(Video $video): Response
    {
        Gate::authorize('delete', $video);

        return response()->noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(VideoUpdateRequest $request, Video $video): VideoResource
    {
        $video = app(UpdateVideoDetails::class)->handle($video, $request->validated());

        return VideoResource::make($video);
    }

    public function destroy(string $id)
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Chapters\Requests\ChapterStoreRequest;
use App\Api\Chapters\Requests\ChapterUpdateRequest;
use Domain\Chapters\Actions\CreateChapter;
use Domain\Chapters\Actions\UpdateChapter;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoChapterController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function store(ChapterStoreRequest $request, Video $video, CreateChapter $createChapter): RedirectResponse
    {
        Gate::authorize('update', $video);

        $chapter = $createChapter->handle($video, $request->safe()->all());

        VideoHasBeenUpdatedEvent::dispatch($video);

        Inertia::flash([
            'title' => $chapter->label,
            'description' => __('The chapter has been created.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function update(ChapterUpdateRequest $request, Video $video, Chapter $chapter, UpdateChapter $updateChapter): RedirectResponse
    {
        Gate::authorize('update', $chapter);

        $updateChapter->handle($chapter, $request->safe()->all());

        VideoHasBeenUpdatedEvent::dispatch($video);

        Inertia::flash([
            'title' => $chapter->label,
            'description' => __('The chapter has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Video $video, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('delete', $chapter);

        $chapter->deleteOrFail();

        VideoHasBeenUpdatedEvent::dispatch($video);

        Inertia::flash([
            'title' => $chapter->label,
            'description' => __('The chapter has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}

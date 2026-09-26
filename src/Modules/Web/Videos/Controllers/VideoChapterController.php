<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Chapters\Actions\CreateChapter;
use Domain\Chapters\Actions\UpdateChapter;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Api\Chapters\Requests\ChapterStoreRequest;
use Modules\Api\Chapters\Requests\ChapterUpdateRequest;

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

        toast(title: (string) $chapter->label, description: __('The chapter has been created.'));

        return back();
    }

    public function update(ChapterUpdateRequest $request, Video $video, Chapter $chapter, UpdateChapter $updateChapter): RedirectResponse
    {
        Gate::authorize('update', $chapter);

        $updateChapter->handle($chapter, $request->safe()->all());

        VideoHasBeenUpdatedEvent::dispatch($video);

        toast(title: (string) $chapter->label, description: __('The chapter has been updated.'));

        return back();
    }

    public function destroy(Video $video, Chapter $chapter): RedirectResponse
    {
        Gate::authorize('delete', $chapter);

        $chapter->deleteOrFail();

        VideoHasBeenUpdatedEvent::dispatch($video);

        toast(title: (string) $chapter->label, description: __('The chapter has been deleted.'), type: 'warning');

        return back();
    }
}

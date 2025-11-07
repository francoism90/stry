<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Models\Tag;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UpdateVideoDetails
{
    public function handle(Video $video, array $attributes): mixed
    {
        return DB::transaction(function () use ($video, $attributes) {
            // Update the video attributes
            $video->updateOrFail(
                Arr::only($attributes, $video->getFillable()),
            );

            // Sync tags if provided
            if (array_key_exists('tags', $attributes)) {
                $tagIds = Tag::query()->options(data_get($attributes, 'tags.*.id', []))->get();

                $video->syncTags($tagIds);
            }

            // Regenerate media conversions if snapshot changed or thumb conversion is missing
            if ($video->wasChanged('snapshot') && $video->hasMedia('clips')) {
                // Get all media IDs associated with the video's clips
                $mediaIds = implode(',', $video->getClipCollection()->modelKeys());

                Artisan::call('media-library:regenerate', [
                    '--ids' => $mediaIds,
                    '--force' => true,
                ]);
            }

            // Dispatch the update event
            VideoHasBeenUpdatedEvent::dispatch($video);
        });
    }
}

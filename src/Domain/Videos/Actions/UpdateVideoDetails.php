<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Models\Tag;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateVideoDetails
{
    public function handle(Video $video, array $attributes): void
    {
        DB::transaction(function () use ($video, $attributes) {
            $video->updateOrFail(
                Arr::only($attributes, $video->getFillable())
            );

            if (array_key_exists('tags', $attributes)) {
                $video->syncTags(Tag::fromOption($attributes['tags'])->toArray());
            }

            if ($video->wasChanged('snapshot') && $video->hasMedia('thumbnail')) {
                $video->getFirstMedia('thumbnail')->delete();
            }

            VideoHasBeenUpdatedEvent::dispatch($video);

            return $video;
        });
    }
}

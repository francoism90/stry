<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Models\Tag;
use Domain\Videos\Jobs\ProcessVideo;
use Domain\Videos\Models\Video;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UpdateVideoDetails
{
    public function handle(Video $video, array $attributes): mixed
    {
        return DB::transaction(function () use ($video, $attributes) {
            $video->updateOrFail(
                Arr::only($attributes, $video->getFillable())
            );

            if (array_key_exists('tags', $attributes)) {
                $video->syncTags(Tag::fromOption($attributes['tags'] ?? [])->get());
            }

            if ($video->wasChanged('snapshot') && $video->hasMedia('thumbnail')) {
                Artisan::call('media-library:regenerate', [
                    'ids' => $video->getClipCollection()->modelKeys(),
                    'only' => 'thumbnail',
                ]);
            }

            // Regenerate the video
            // ProcessVideo::dispatch($video);
        });
    }
}

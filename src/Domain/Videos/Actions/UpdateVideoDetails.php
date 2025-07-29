<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Actions\SyncModelTags;
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
                app(SyncModelTags::class)->handle($video, $attributes['tags']);
            }

            return $video;
        });
    }
}

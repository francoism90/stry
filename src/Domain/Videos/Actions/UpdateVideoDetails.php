<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Jobs\OptimizeVideo;
use Domain\Videos\Models\Video;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateVideoDetails
{
    public function execute(Video $model, array $attributes): void
    {
        DB::transaction(function () use ($model, $attributes) {
            $model->updateOrFail(
                Arr::only($attributes, $model->getFillable())
            );

            if (array_key_exists('tags', $attributes)) {
                $tags = collect(data_get($attributes['tags'], '*.id', []));

                $model->syncTags($tags);
            }

            OptimizeVideo::dispatchIf(
                $model->wasChanged('snapshot'),
                $model
            );
        });
    }
}

<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class UpdateMediaDetails
{
    public function handle(Media $media, array $attributes): mixed
    {
        return DB::transaction(function () use ($media, $attributes) {
            // Update the media attributes
            $media->updateOrFail(
                Arr::only($attributes, $media->getFillable()),
            );

            // Trigger event to handle any post-update actions
            event(new MediaHasBeenAddedEvent($media));
        });
    }
}

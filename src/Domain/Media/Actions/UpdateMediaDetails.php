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
        // Decode the custom properties JSON string before it hits the array cast,
        // otherwise it gets re-encoded as a JSON-encoded string instead of an object.
        if (is_string($attributes['custom_properties'] ?? null)) {
            $attributes['custom_properties'] = json_decode($attributes['custom_properties'], true);
        }

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

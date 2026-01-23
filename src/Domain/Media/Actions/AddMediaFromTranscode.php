<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Domain\Media\Models\Transcode;
use Illuminate\Database\Eloquent\Model;

class AddMediaFromTranscode
{
    public function handle(Media $media): void
    {
        // Get the model associated with the media
        $model = $media->model;

        if (! $model instanceof Model) {
            return;
        }

        // Get all completed transcodes for this media
        $completedTranscodes = $media->transcodes()
            ->completed()
            ->get();

        // Add each successful transcode to the model's media collection
        foreach ($completedTranscodes as $transcode) {
            $model
                ->addMediaFromDisk($transcode->getOutputPath(), Transcode::getDisk())
                ->toMediaCollection('clips');
        }
    }
}

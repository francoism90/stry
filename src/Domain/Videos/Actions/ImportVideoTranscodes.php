<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Transcode;
use Domain\Videos\Models\Video;

class ImportVideoTranscodes
{
    public function handle(Video $video): void
    {
        // Get the associated media
        if (! $media = $video->media) {
            return;
        }

        // Get all completed transcodes by (if any)
        $transcodes = $media
            ->transcodes()
            ->completed()
            ->get();

        // Add each successful transcode to the model's media collection
        foreach ($transcodes as $transcode) {
            $video
                ->addMediaFromDisk($transcode->getOutputPath(), Transcode::getDisk())
                ->toMediaCollection('clips');
        }

        // Delete all transcodes after adding them to the media collection
        $media->transcodes()->delete();
    }
}

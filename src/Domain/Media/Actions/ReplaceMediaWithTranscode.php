<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Illuminate\Database\Eloquent\Model;

class ReplaceMediaWithTranscode
{
    public function handle(Transcode $transcode): void
    {
        throw_unless(
            $transcode->isCompleted(),
            \RuntimeException::class,
            'Cannot replace media with incomplete transcode'
        );

        // Get media associated with the transcode
        $media = $transcode->media;

        // Get model and storage disks
        $model = $media->model;

        if (! $model instanceof Model) {
            return;
        }

        // Add the transcoded media to the model's media collection
        $model
            ->addMediaFromDisk($transcode->getOutputPath(), Transcode::getDisk())
            ->toMediaCollection('clips');

        // Delete the original media file from storage
        $media->deleteOrFail();
    }
}

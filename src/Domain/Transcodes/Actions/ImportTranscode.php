<?php

declare(strict_types=1);

namespace Domain\Transcodes\Actions;

use Domain\Transcodes\Models\Transcode;
use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Jobs\ImportVideo;

class ImportTranscode
{
    public function handle(Transcode $transcode): void
    {
        // Get associated model
        $model = $transcode->transcodable;

        // Create a data object for the transcode file
        $file = VideoFile::from([
            'disk' => $transcode->getDisk(),
            'path' => $transcode->getOutputPath(),
        ]);

        // Dispatch the appropriate import job based on the model type
        match ($model->getMorphClass()) {
            'video' => ImportVideo::dispatch($model, $file),
            default => throw new \InvalidArgumentException('Unsupported transcodable type'),
        };

        // Mark the transcode as imported
        $transcode->markAsImported();
    }
}

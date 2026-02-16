<?php

declare(strict_types=1);

namespace Domain\Transcodes\Actions;

use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Jobs\ImportVideo;

class ImportTranscode
{
    public function handle(Transcode $transcode): void
    {
        // Get associated model
        $model = $transcode->transcodable;

        // Dispatch the appropriate import job based on the model type
        match ($model->getMorphClass()) {
            'video' => ImportVideo::dispatch($model, $transcode->getDisk(), $transcode->getOutputPath()),
            default => throw new \InvalidArgumentException('Unsupported transcodable type'),
        };

        // Mark the transcode as imported
        $transcode->markAsImported();
    }
}

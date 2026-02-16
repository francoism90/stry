<?php

declare(strict_types=1);

namespace Domain\Transcodes\Actions;

use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Jobs\ImportVideo;

class ImportTranscode
{
    public function handle(Transcode $transcode): array
    {
        // Get associated model
        $model = $transcode->transcodable;

        match ($model->getMorphClass()) {
            'video' => ImportVideo::dispatch($model, $transcode->getDisk(), $transcode->getOutputPath()),
            default => throw new \InvalidArgumentException('Unsupported transcodable type'),
        };

        return [
            'success' => true,
            'message' => 'Transcode imported successfully.',
        ];
    }
}

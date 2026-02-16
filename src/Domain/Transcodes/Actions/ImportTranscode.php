<?php

declare(strict_types=1);

namespace Domain\Transcodes\Actions;

use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;
use Domain\Videos\Jobs\ImportVideo;

class ImportTranscode
{
    public function handle(User $user, Transcode $transcode): array
    {
        $disk = $transcode->getDisk();
        $path = $transcode->getOutputPath();

        match ($transcode->transcodable_type) {
            'video' => ImportVideo::dispatch($user, $disk, $path),
            default => throw new \InvalidArgumentException('Unsupported transcodable type: '.$transcode->transcodable_type),
        };

        return [
            'success' => true,
            'message' => 'Transcode imported successfully.',
        ];
    }
}

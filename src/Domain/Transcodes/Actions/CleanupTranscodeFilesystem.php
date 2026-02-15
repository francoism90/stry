<?php

declare(strict_types=1);

namespace Domain\Transcodes\Actions;

use Domain\Transcodes\Models\Transcode;

class CleanupTranscodeFilesystem
{
    public function handle(Transcode $transcode): void
    {
        // Delete the transcode directory
        if ($transcode->getFilesystem()->directoryExists($transcode->getPath())) {
            $transcode->getFilesystem()->deleteDirectory($transcode->getPath());
        }
    }
}

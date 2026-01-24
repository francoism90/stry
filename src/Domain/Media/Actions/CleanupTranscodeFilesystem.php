<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;

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

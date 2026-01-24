<?php

declare(strict_types=1);

namespace Domain\Media\Observers;

use Domain\Media\Actions\CleanupTranscodeFilesystem;
use Domain\Media\Models\Transcode;

class TranscodeObserver
{
    public function deleted(Transcode $transcode): void
    {
        if (method_exists($transcode, 'isForceDeleting') && ! $transcode->isForceDeleting()) {
            return;
        }

        app(CleanupTranscodeFilesystem::class)->handle($transcode);
    }
}

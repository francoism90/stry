<?php

declare(strict_types=1);

namespace Domain\Transcodes\Observers;

use Domain\Transcodes\Actions\CleanupTranscodeFilesystem;
use Domain\Transcodes\Models\Transcode;

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

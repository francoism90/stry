<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Illuminate\Support\Facades\Storage;

class ReplaceMediaWithTranscode
{
    public function handle(Transcode $transcode): void
    {
        throw_unless(
            $transcode->isCompleted(),
            \RuntimeException::class,
            'Cannot replace media with incomplete transcode'
        );

        $media = $transcode->media;

        $mediaDisk = Storage::disk($media->disk);
        $transcodeDisk = Storage::disk(Transcode::getDisk());

        $originalPath = $media->getPath();
        $transcodedPath = $transcode->getOutputPath();

        // Create backup of original file
        $backupPath = preg_replace('/(\.[^.]+)$/', '_original$1', $originalPath);
        $mediaDisk->copy($originalPath, $backupPath);

        // Replace original file with transcoded version
        $mediaDisk->put($originalPath, $transcodeDisk->get($transcodedPath));

        // Update media record with new file properties
        $media->updateOrFail([
            'mime_type' => 'video/mp4',
            'size' => $mediaDisk->size($originalPath),
        ]);

        // Cleanup - remove backup, transcoded file, and transcode record
        $mediaDisk->delete($backupPath);
        $transcodeDisk->delete($transcodedPath);

        $transcode->deleteOrFail();
    }
}

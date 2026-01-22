<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Illuminate\Support\Facades\Storage;

class ReplaceMediaWithTranscode
{
    public function handle(Transcode $transcode): void
    {
        if (! $transcode->isCompleted()) {
            throw new \RuntimeException('Cannot replace media with incomplete transcode');
        }

        $media = $transcode->media;
        $disk = Storage::disk($media->disk);

        $originalPath = $media->getPath();
        $transcodedPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.
            pathinfo($originalPath, PATHINFO_FILENAME).'_av1.mp4';

        // Backup original
        $backupPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.
            pathinfo($originalPath, PATHINFO_FILENAME).'_original.'.
            pathinfo($originalPath, PATHINFO_EXTENSION);

        $disk->move($originalPath, $backupPath);

        // Replace with transcoded version
        $disk->move($transcodedPath, $originalPath);

        // Update media record
        $media->update([
            'mime_type' => 'video/mp4',
            'size' => $disk->size($originalPath),
        ]);

        // Delete transcode record
        $transcode->delete();
    }
}

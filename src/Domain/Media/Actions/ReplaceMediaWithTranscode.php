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

        $disk = Storage::disk($media->disk);
        $transcodeDisk = Storage::disk(Transcode::getDisk());

        $originalPath = $media->getPath();
        $pathInfo = pathinfo($originalPath);

        $transcodedPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_av1.mp4';
        $backupPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_original.'.$pathInfo['extension'];

        // Backup original file
        $disk->move($originalPath, $backupPath);

        // Copy transcoded file from transcodes disk to media disk
        $disk->put($originalPath, $transcodeDisk->get($transcodedPath));

        $transcodeDisk->delete($transcodedPath);

        // Update media record
        $media->updateOrFail([
            'mime_type' => 'video/mp4',
            'size' => $disk->size($originalPath),
        ]);

        // Delete transcode record
        $transcode->deleteOrFail();
    }
}

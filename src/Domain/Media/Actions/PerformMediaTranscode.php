<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Foxws\AV1\Facades\AV1;

class PerformMediaTranscode
{
    public function handle(Transcode $transcode): void
    {
        if ($transcode->encoder !== 'ab-av1') {
            throw new \InvalidArgumentException('Unsupported encoder: '.$transcode->encoder);
        }

        // Get associated media
        $media = $transcode->media;

        // Define output path
        $outputPath = $transcode->getOutputPath();

        // Perform AV1 encoding
        $encoder = AV1::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->abav1()
            ->vmafEncode();

        // Save to the specified disk and path
        $result = $encoder
            ->export()
            ->toDisk(Transcode::getDisk())
            ->save($outputPath);

        throw_unless(
            $result->isSuccessful(),
            \RuntimeException::class,
            'AV1 encoding failed: '.$result->getErrorOutput()
        );

        // Clean up temporary files
        $encoder->cleanupTemporaryFiles();
    }
}

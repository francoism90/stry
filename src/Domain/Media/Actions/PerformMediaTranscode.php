<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Foxws\AV1\Facades\AV1;
use InvalidArgumentException;
use Throwable;

class PerformMediaTranscode
{
    public function handle(Transcode $transcode): void
    {
        throw_if(
            $transcode->encoder !== 'ab-av1',
            new InvalidArgumentException('Unsupported encoder: '.$transcode->encoder)
        );

        // Get the associated media
        $media = $transcode->media;

        // Get the output path for the transcode
        $outputPath = $transcode->getOutputPath();

        // Create the AV1 encoder instance
        $encoder = AV1::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->abav1()
            ->vmafEncode();

        try {
            // Mark the transcode as processing
            $transcode->markAsProcessing();

            // Export the encoded media to the specified disk and path
            $encoder
                ->export()
                ->toDisk(Transcode::getDisk())
                ->save($outputPath);

            // Get the size of the output file
            $fileSize = $transcode->getFilesystem()->size($transcode->getOutputPath());

            // Mark the transcode as completed
            $transcode->markAsCompleted($fileSize);
        } catch (Throwable $exception) {
            // Mark the transcode as failed
            $transcode->markAsFailed($exception->getMessage());

            throw $exception;
        } finally {
            // Clean up temporary files used during encoding
            $encoder->cleanupTemporaryFiles();
        }
    }
}

<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Foxws\AbAv1\Facades\AbAv1;
use Foxws\AV1\Facades\AV1;
use Throwable;

class CreateNewVideoTranscode
{
    public function handle(Video $video): void
    {
        // Check if the video is currently transcoding or if it doesn't have any clips
        if ($video->hasTranscode() || ! $video->hasMedia('clips')) {
            return;
        }

        // Get the first clip associated with the video
        $clip = $video->getClipCollection()->first();

        /** @var Playlist $playlist */
        $transcode = $video->createTranscode([
            'encoder' => TranscodeEncoder::AV1,
        ]);

        // Create the AV1 encoder instance
        $encoder = AbAv1::fromDisk($clip->disk)
            ->open($clip->getPathRelativeToRoot())
            ->withPreset(8)
            ->withMinVMAF(95)
            ->autoEncode();

        try {
            // Mark the transcode as processing
            $transcode->markAsProcessing();

            // Export the encoded media to the specified disk and path
            $encoder
                ->export()
                ->toDisk($transcode->getDisk())
                ->save($transcode->getPath());

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

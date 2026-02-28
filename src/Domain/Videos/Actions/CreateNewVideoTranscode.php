<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Foxws\AbAv1\Facades\AbAv1;
use Illuminate\Support\Collection;
use Throwable;

class CreateNewVideoTranscode
{
    public function handle(Video $video): Collection
    {
        // Check if the video is currently transcoding or if it doesn't have any clips
        if ($video->hasTranscode() || ! $video->hasMedia('clips')) {
            return Collection::empty();
        }

        // Get the collection of clips for the video, grouped by disk
        $clips = $video->getClips();

        return $clips->map(function (Media $media) use ($video) {
            // Initialize ab-av1 encoder
            $encoder = AbAv1::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

            /** @var Transcode $transcode */
            $transcode = $video->createTranscode([
                'file_name' => pathinfo($media->file_name, PATHINFO_FILENAME).'.mp4',
                'encoder' => TranscodeEncoder::AV1,
            ]);

            // Transcode the video
            try {
                // Mark the transcode as processing
                $transcode->markAsProcessing();

                // Encode and export (like Laravel Streamer - export() starts the process)
                $encoder
                    ->export()
                    ->toDisk($transcode->getDisk())
                    ->toPath($transcode->getOutputPath())
                    ->afterSaving(fn () => $transcode->markAsCompleted())
                    ->save();
            } catch (Throwable $exception) {
                // Mark the transcode as failed
                $transcode->markAsFailed($exception->getMessage());

                throw $exception;
            } finally {
                // Clean up temporary files used during encoding
                $encoder->cleanupTemporaryFiles();
            }

            return $transcode;
        });
    }
}

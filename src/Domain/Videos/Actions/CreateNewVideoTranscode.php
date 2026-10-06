<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Foxws\Media\Facades\Media as MediaFactory;
use Foxws\Media\Process\Progress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateNewVideoTranscode
{
    /**
     * @return Collection<int, Transcode>
     */
    public function handle(Video $video): Collection
    {
        // Check if the video is currently transcoding or if it doesn't have any clips
        if ($video->hasTranscode() || ! $video->hasMedia('clips')) {
            return Collection::empty();
        }

        // Get the collection of clips for the video, grouped by disk
        $clips = $video->getClips();

        return $clips->map(function (Media $media) use ($video) {
            /** @var Transcode $transcode */
            $transcode = $video->createTranscode([
                'file_name' => pathinfo($media->file_name, PATHINFO_FILENAME).'.mp4',
                'encoder' => TranscodeEncoder::AV1,
            ]);

            try {
                $transcode->markAsProcessing();

                // Verify and fail fast, so a damaged or truncated upload fails
                // instead of saving a broken transcode.
                MediaFactory::fromDisk($media->disk)
                    ->open($media->getPathRelativeToRoot())
                    ->abAv1()
                    ->withVerify()
                    ->withFailFast()
                    ->withContext(['video_id' => $video->getKey(), 'transcode_id' => $transcode->getKey()])
                    ->onProgress(fn (Progress $progress) => $this->logProgress($transcode, $progress))
                    ->toDisk($transcode->getDisk())
                    ->afterSaving(fn () => $transcode->markAsCompleted())
                    ->save($transcode->getOutputPath());
            } catch (Throwable $exception) {
                $transcode->markAsFailed($exception->getMessage());

                throw $exception;
            }

            return $transcode;
        });
    }

    /**
     * ab-av1 reports progress after 16, 32, 64 seconds and so on, so this logs a few lines per encode.
     */
    protected function logProgress(Transcode $transcode, Progress $progress): void
    {
        Log::info('Transcoding video', [
            'transcode_id' => $transcode->getKey(),
            'percentage' => $progress->percentage(),
            'remaining' => $progress->remaining(),
            'fps' => $progress->fps,
        ]);
    }
}

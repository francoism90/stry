<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Foxws\AV1\Facades\AV1;
use Illuminate\Support\Facades\Log;

class ConvertMediaToAv1
{
    public function handle(Transcode $transcode): void
    {
        $media = $transcode->media;

        $options = $transcode->getOptions();

        $inputPath = $media->getPath();

        $directory = pathinfo($inputPath, PATHINFO_DIRNAME);
        $filename = pathinfo($inputPath, PATHINFO_FILENAME).'_av1.mp4';
        $outputPath = $directory.'/'.$filename;

        $result = AV1::fromDisk($media->disk)
            ->open($inputPath)
            ->vmafEncode()
            ->preset((string) ($options['preset'] ?? '6'))
            ->minVmaf((int) ($options['min_vmaf'] ?? 95))
            ->export()
            ->toDisk(Transcode::getDisk())
            ->afterSaving(function ($result, $path) use ($transcode) {
                Log::info('AV1 encoding completed', [
                    'transcode_id' => $transcode->id,
                    'path' => $path,
                    'exit_code' => $result->getExitCode(),
                ]);
            })
            ->save($outputPath);

        if (! $result->isSuccessful()) {
            throw new \RuntimeException(
                'AV1 encoding failed: '.$result->getErrorOutput()
            );
        }
    }
}

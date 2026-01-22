<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Foxws\AV1\Facades\AV1;
use Illuminate\Support\Str;

class ConvertMediaToAv1
{
    public function handle(Transcode $transcode): void
    {
        $media = $transcode->media;
        $options = $transcode->getOptions();

        $filename = pathinfo($media->getPath(), PATHINFO_FILENAME).'_av1.mp4';
        $outputPath = Str::uuid().'/'.$filename;

        $result = AV1::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->abav1()
            ->vmafEncode()
            ->preset((string) ($options['preset'] ?? '6'))
            ->minVmaf((int) ($options['min_vmaf'] ?? 90))
            ->maxEncodedPercent((int) ($options['max_encoded_percent'] ?? 150))
            ->export()
            ->toDisk(Transcode::getDisk())
            ->save($outputPath);

        if (! $result->isSuccessful()) {
            throw new \RuntimeException(
                'AV1 encoding failed: '.$result->getErrorOutput()
            );
        }
    }
}

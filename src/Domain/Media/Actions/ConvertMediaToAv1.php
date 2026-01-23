<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Foxws\AV1\Facades\AV1;

class ConvertMediaToAv1
{
    public function handle(Transcode $transcode): void
    {
        $media = $transcode->media;

        $options = $transcode->getOptions();

        $outputPath = $transcode->getOutputPath();

        // Perform AV1 encoding
        $encoder = AV1::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->abav1()
            ->vmafEncode()
            ->preset((string) ($options['preset'] ?? '6'))
            ->minVmaf((int) ($options['min_vmaf'] ?? 90))
            ->maxEncodedPercent((int) ($options['max_encoded_percent'] ?? 150))
            ->export()
            ->toDisk(Transcode::getDisk())
            ->save($outputPath);

        throw_unless(
            $encoder->isSuccessful(),
            \RuntimeException::class,
            'AV1 encoding failed: '.$encoder->getErrorOutput()
        );

        // Clean up temporary files
        $encoder->cleanupTemporaryFiles();
    }
}

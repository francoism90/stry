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

        $outputPath = $transcode->getOutputPath();

        // Perform AV1 encoding
        $encoder = AV1::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->abav1()
            ->vmafEncode()
            ->preset('6')
            ->minVmaf(80)
            ->maxEncodedPercent(300);

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

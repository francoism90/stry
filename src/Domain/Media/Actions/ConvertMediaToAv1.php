<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Transcode;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelAv1\Av1;

class ConvertMediaToAv1
{
    public function handle(Transcode $transcode, ?string $preset = null): void
    {
        $media = $transcode->media;

        $inputPath = Storage::disk($media->disk)->path($media->getPath());
        $outputPath = Storage::disk($media->disk)->path(
            pathinfo($media->getPath(), PATHINFO_DIRNAME).'/'.
            pathinfo($media->getPath(), PATHINFO_FILENAME).'_av1.mp4'
        );

        Av1::create($inputPath)
            ->preset($preset ?? 'medium')
            ->onProgress(function (int $percentage) use ($transcode) {
                $transcode->update(['progress' => $percentage]);
            })
            ->save($outputPath);
    }
}

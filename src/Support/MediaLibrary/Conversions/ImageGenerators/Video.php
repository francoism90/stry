<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video as ImageGenerator;

class Video extends ImageGenerator
{
    public function supportedExtensions(): Collection
    {
        return collect([
            'webm', 'mov', 'mkv', 'mp4', 'm4v',
            'mkv', 'mk3d', 'ogv', 'ivf', 'movie',
            'qt', 'avi', 'wmv', 'mpeg', 'mpg',
        ]);
    }

    public function supportedMimeTypes(): Collection
    {
        return collect([
            'video/av1',
            'video/matroska-3d',
            'video/mp4',
            'video/mp4v-es',
            'video/mpeg',
            'video/ogg',
            'video/quicktime',
            'video/VP8',
            'video/VP9',
            'video/webm',
            'video/x-indeo',
            'video/x-ivf',
            'video/x-m4v',
            'video/x-matroska',
            'video/x-msvideo',
        ]);
    }
}

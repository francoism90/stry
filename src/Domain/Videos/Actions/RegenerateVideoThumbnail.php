<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Artisan;

class RegenerateVideoThumbnail
{
    public function execute(Video $video): void
    {
        $clips = $video->getClipCollection();

        if ($clips->isEmpty()) {
            return;
        }

        Artisan::call('media-library:regenerate', [
            '--ids' => implode(',', $clips->modelKeys()),
            '--only' => 'thumbnail',
            '--force' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Jobs\ConvertMediaJob;
use Domain\Media\Models\Media;
use Domain\Media\Models\Transcode;
use Domain\Media\States\Pending;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Config;

class CreateMediaTranscode
{
    public function handle(Video $video, Media $media, ?string $preset = null): Transcode
    {
        $transcode = Transcode::create([
            'media_id' => $media->id,
            'preset' => $preset ?? Config::string('transcodes.default'),
            'state' => Pending::class,
        ]);

        ConvertMediaJob::dispatch($transcode);

        return $transcode;
    }
}

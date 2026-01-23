<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Jobs\ConvertMediaJob;
use Domain\Media\Models\Media;
use Domain\Media\Models\Transcode;
use Domain\Media\States\Pending;
use Illuminate\Support\Facades\Config;

class CreateMediaTranscode
{
    public function handle(Media $media, ?string $preset = null): Transcode
    {
        // Check if there's already a pending or processing transcode for this media
        $existingTranscode = $media->transcodes()
            ->inProgress()
            ->first();

        if ($existingTranscode) {
            return $existingTranscode;
        }

        // Create a new transcode record
        $transcode = $media->transcodes()->create([
            'preset' => $preset ?? Config::string('transcodes.default'),
            'state' => Pending::class,
        ]);

        // Dispatch the conversion job
        ConvertMediaJob::dispatch($transcode);

        return $transcode;
    }
}

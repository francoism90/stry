<?php

declare(strict_types=1);

namespace Domain\Transcodes\Commands;

use Domain\Videos\Jobs\TranscodeVideo;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\warning;

class CreateTranscodeCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'transcodes:create';

    /**
     * @var string
     */
    protected $description = 'Dispatch a transcode job for a video';

    public function handle(): void
    {
        $videoId = search(
            label: 'Select a video to transcode',
            placeholder: 'e.g. My Video Title',
            options: fn (string $value) => strlen($value) > 0
                ? Video::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : Video::limit(10)->pluck('name', 'id')->all(),
        );

        $video = Video::findOrFail($videoId);

        if ($video->hasTranscode()) {
            warning("Video `{$video->name}` already has an active transcode.");

            return;
        }

        TranscodeVideo::dispatch($video);

        info("Transcode job queued for `{$video->name}`.");
    }
}

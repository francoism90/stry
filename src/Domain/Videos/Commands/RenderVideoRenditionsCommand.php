<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Jobs\RenderVideoRenditions;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class RenderVideoRenditionsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'videos:renditions
        {video?* : The IDs of the videos to render; every video by default}
        {--force : Skip the confirmation prompt}';

    /**
     * @var string
     */
    protected $description = 'Queue the renditions of video clips, as chosen in the playback settings';

    public function handle(): int
    {
        $ids = (array) $this->argument('video');

        $videos = Video::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'clips'))
            ->when($ids !== [], fn ($query) => $query->whereKey($ids))
            ->get();

        if ($videos->isEmpty()) {
            info('No videos found to render.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Queue the renditions of {$videos->count()} videos?")) {
            return self::SUCCESS;
        }

        $videos->each(fn (Video $video) => RenderVideoRenditions::dispatch($video));

        info("Queued the renditions of {$videos->count()} videos.");

        return self::SUCCESS;
    }
}

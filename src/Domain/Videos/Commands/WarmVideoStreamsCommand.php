<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Jobs\WarmVideoStream;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class WarmVideoStreamsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:warm
        {video?* : The IDs of the videos to warm; every video by default}
        {--force : Skip the confirmation prompt}';

    /**
     * @var string
     */
    protected $description = 'Queue the streams of videos to be probed and indexed before they are played';

    public function handle(): int
    {
        $ids = (array) $this->argument('video');

        $videos = Video::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'clips'))
            ->when($ids !== [], fn ($query) => $query->whereKey($ids));

        $count = $videos->count();

        if ($count === 0) {
            info('No videos found to warm.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Warm the streams of {$count} videos?")) {
            return self::SUCCESS;
        }

        $videos->lazyById()->each(fn (Video $video) => WarmVideoStream::dispatch($video));

        info("Queued the streams of {$count} videos.");

        return self::SUCCESS;
    }
}

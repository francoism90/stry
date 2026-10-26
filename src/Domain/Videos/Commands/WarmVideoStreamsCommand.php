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
        {--after= : Only videos with a higher ID, to continue where a limited run stopped}
        {--limit= : The most videos to queue, in order of ID}
        {--force : Skip the confirmation prompt}';

    /**
     * @var string
     */
    protected $description = 'Queue the streams of videos to be probed and indexed before they are played';

    public function handle(): int
    {
        $ids = (array) $this->argument('video');

        $after = $this->option('after');
        $limit = $this->option('limit');

        $videos = Video::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'clips'))
            ->when($ids !== [], fn ($query) => $query->whereKey($ids))
            ->when(filled($after), fn ($query) => $query->where('id', '>', (int) $after));

        $count = filled($limit) ? min($videos->count(), max(0, (int) $limit)) : $videos->count();

        if ($count === 0) {
            info('No videos found to warm.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Warm the streams of {$count} videos?")) {
            return self::SUCCESS;
        }

        $last = null;

        $videos->lazyById()->take($count)->each(function (Video $video) use (&$last): void {
            WarmVideoStream::dispatch($video);

            $last = $video->getKey();
        });

        info("Queued the streams of {$count} videos.");

        if (filled($limit) && $last !== null && $videos->clone()->where('id', '>', $last)->exists()) {
            info("Continue with --after={$last}.");
        }

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Jobs\GenerateVideoReel;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Collection;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;

class RegenerateVideoReelsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:reels
        {video?* : The IDs of the videos to regenerate; every video by default}
        {--missing : Only videos without a reel}
        {--max-duration= : Only videos up to this many seconds long}
        {--force : Skip the confirmation prompt}';

    /**
     * @var string
     */
    protected $description = 'Queue the reels of videos to be regenerated';

    public function handle(): int
    {
        $ids = (array) $this->argument('video');
        $maxDuration = $this->option('max-duration');

        $videos = Video::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'clips'))
            ->when($ids !== [], fn ($query) => $query->whereKey($ids))
            ->when($this->option('missing'), fn ($query) => $query->whereDoesntHave('media', fn ($query) => $query->where('collection_name', 'reels')))
            ->with('media')
            ->get()
            ->when($maxDuration !== null, fn (Collection $videos) => $videos->filter(fn (Video $video): bool => $video->duration <= (float) $maxDuration));

        if ($videos->isEmpty()) {
            info('No videos found to regenerate.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Regenerate the reels of {$videos->count()} videos?")) {
            return self::SUCCESS;
        }

        $videos->each(fn (Video $video) => GenerateVideoReel::dispatch($video));

        info("Queued the reels of {$videos->count()} videos.");

        return self::SUCCESS;
    }
}

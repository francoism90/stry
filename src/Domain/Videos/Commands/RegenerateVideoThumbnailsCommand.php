<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\ExtractVideoThumbnails;
use Foxws\Media\MediaFactory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\warning;

class RegenerateVideoThumbnailsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:thumbnails
        {video?* : The IDs of the videos to regenerate; every video by default}
        {--shorter-than= : Only clips of at most this many seconds}
        {--force : Skip the confirmation prompt}';

    /**
     * @var string
     */
    protected $description = 'Regenerate the seek preview thumbnails of video clips';

    public function handle(ExtractVideoThumbnails $thumbnails, MediaFactory $media): int
    {
        $ids = (array) $this->argument('video');
        $shorterThan = $this->option('shorter-than');

        $videos = Video::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'clips'))
            ->when($ids !== [], fn ($query) => $query->whereKey($ids))
            ->get()
            ->filter(function (Video $video) use ($media, $shorterThan): bool {
                if (! is_numeric($shorterThan)) {
                    return true;
                }

                $clip = $video->getClips()->first();

                return $clip !== null && rescue(fn () => $media->fromDisk($clip->disk)->open($clip->getPathRelativeToRoot())->probe()->duration() <= (float) $shorterThan, false, report: false);
            });

        if ($videos->isEmpty()) {
            info('No videos found to regenerate.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! confirm("Regenerate the thumbnails of {$videos->count()} videos?")) {
            return self::SUCCESS;
        }

        $failed = 0;

        progress(
            label: 'Regenerating thumbnails',
            steps: $videos,
            callback: function (Video $video) use ($thumbnails, &$failed): void {
                $clip = $video->getClips()->first();

                if ($clip === null || $thumbnails->extract($clip) === null) {
                    $failed++;
                }
            },
        );

        if ($failed > 0) {
            warning("{$failed} videos could not be sampled.");
        }

        info("Regenerated the thumbnails of {$videos->count()} videos.");

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Number;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ClearCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:clear';

    /**
     * @var string
     */
    protected $description = 'Delete all trashed videos';

    public function handle(): void
    {
        $videos = spin(
            message: 'Retrieving deleted videos...',
            callback: fn () => Video::onlyTrashed()->lazy()
        );

        if ($videos->isEmpty()) {
            info('No videos found for deletion.');

            return;
        }

        table(
            headers: ['ID', 'Name', 'File Size'],
            rows: $videos->map(fn (Video $video) => [
                $video->getKey(),
                $video->name,
                Number::fileSize($video->file_size),
            ])->all()
        );

        if (confirm('Are you sure you want to delete these videos?')) {
            $videos->each(function (Video $model) {
                if (! $model->trashed()) {
                    return;
                }

                info("deleting {$model->name} ({$model->getKey()})");

                $model->forceDelete();
            });
        }
    }
}

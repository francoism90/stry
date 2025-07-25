<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'videos:clean')]
class CleanCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:clean';

    /**
     * @var string
     */
    protected $description = 'Delete all trashed videos';

    public function handle(): void
    {
        $items = Video::onlyTrashed()->lazy();

        // TODO: show table with items that will be deleted

        if ($items->isEmpty()) {
            $this->components->info('No videos found for deletion');

            return;
        }

        throw_if(! $this->confirm("Are you sure to delete {$items->count()} videos?"));

        $items->each(function (Video $model) {
            if (! $model->trashed()) {
                return;
            }

            $this->components->info("deleting {$model->name} ({$model->getKey()})");

            $model->forceDelete();
        });
    }
}

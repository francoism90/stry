<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class CleanVideos extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:clean';

    /**
     * @var string
     */
    protected $description = 'Delete trashed videos';

    public function handle(): void
    {
        // TODO: only temp!
        Video::all()->each(function (Video $model) {
            $model->forceDelete();
        });

        // $items = Video::onlyTrashed()->lazy();

        // if ($items->isEmpty()) {
        //     $this->components->info('No videos found for deletion');

        //     return;
        // }

        // throw_if(! $this->confirm("Are you sure to delete {$items->count()} videos?"));

        // $items->each(function (Video $model) {
        //     if ($model->trashed()) {
        //         $this->components->info("deleting {$model->name} ({$model->getKey()})");
        //         $model->forceDelete();
        //     }
        // });
    }
}

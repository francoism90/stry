<?php

declare(strict_types=1);

namespace Domain\Tags\Commands;

use Domain\Tags\Actions\SetTagsOrder;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SortTags extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'tags:sort';

    /**
     * @var string
     */
    protected $description = 'Set tags order';

    public function handle(): void
    {
        app(SetTagsOrder::class)->execute();

        $this->components->info('Tags have been sorted successfully.');
    }
}

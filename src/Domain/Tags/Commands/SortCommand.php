<?php

declare(strict_types=1);

namespace Domain\Tags\Commands;

use Domain\Tags\Actions\SetTagsOrder;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;

class SortCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'tags:sort';

    /**
     * @var string
     */
    protected $description = 'Set tags in order';

    public function handle(): void
    {
        app(SetTagsOrder::class)->handle();

        info('Tags have been sorted successfully.');
    }
}

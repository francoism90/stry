<?php

declare(strict_types=1);

namespace Domain\Tags\Commands;

use Domain\Tags\Actions\SetTagsOrder;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;

class SortTagsCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'tags:sort';

    /**
     * @var string
     */
    protected $description = 'Set tags in order';

    public function handle(SetTagsOrder $action): void
    {
        if ($action->handle()) {
            info('Tags have been sorted successfully.');
        }
    }
}

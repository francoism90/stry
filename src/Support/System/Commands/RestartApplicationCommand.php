<?php

declare(strict_types=1);

namespace Support\System\Commands;

use Domain\Shared\Actions\RestartApplication;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;

class RestartApplicationCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:restart';

    /**
     * @var string
     */
    protected $description = 'Restart the Octane and queue workers to apply changed settings';

    public function handle(RestartApplication $restartApplication): void
    {
        $restartApplication->handle();

        info('The queue workers restart after their current job, and Octane reloads its workers.');
    }
}

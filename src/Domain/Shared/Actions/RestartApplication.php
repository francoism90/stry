<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

use function Illuminate\Support\php_binary;

class RestartApplication
{
    /**
     * Restart the queue workers and the Octane workers, so they boot again with the new settings.
     *
     * Queue workers pick up the restart signal from the cache once their current job is done, so
     * this works from any container. "horizon:terminate" isn't used: it signals process IDs, which
     * belong to another container. Octane is reloaded from a detached process, because the reload
     * waits for the running requests to finish, including the one that asked for it.
     */
    public function handle(): void
    {
        Artisan::call('queue:restart');

        Process::path(base_path())->run(sprintf(
            'nohup %s -d variables_order=EGPCS artisan octane:reload > /dev/null 2>&1 &',
            escapeshellarg(php_binary()),
        ));
    }
}

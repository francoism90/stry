<?php

declare(strict_types=1);

namespace Support\System\Commands;

use Domain\Shared\Actions\RestartApplication;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;

class UpdateApplicationCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'app:update
        {--scout : Sync the search indexes and import their records}
        {--no-restart : Don\'t restart the Octane and queue workers}';

    /**
     * @var string
     */
    protected $description = 'Run the migrations and restart the workers after upgrading';

    public function handle(RestartApplication $restartApplication): int
    {
        if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->call('settings:clear-cache');

        if ($this->option('scout')) {
            $this->call('scout:sync', ['--import' => true]);
        }

        if ($this->option('no-restart')) {
            info('stry is up to date.');

            return self::SUCCESS;
        }

        $restartApplication->handle();

        info('stry is up to date. The queue workers restart after their current job, and Octane reloads its workers.');

        return self::SUCCESS;
    }
}

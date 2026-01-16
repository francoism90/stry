<?php

declare(strict_types=1);

namespace Support\Scout\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;

use function Laravel\Prompts\info;

class SyncCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'scout:sync {--delete} {--import}';

    /**
     * @var string
     */
    protected $description = 'Sync Typesense indexes';

    public function handle(): void
    {
        $indexes = Config::collection('scout.typesense.model-settings');

        if ($indexes->isEmpty()) {
            info('No indexes configured to sync');
        }

        $indexes->each(function (array $settings, string $model) {
            // Delete existing index
            if ($this->option('delete')) {
                $this->call('scout:delete-index', ['name' => $model]);
            }

            // Recreate index
            $this->call('scout:index', ['name' => $model]);

            // Import records
            if ($this->option('import')) {
                $this->call('scout:import', ['model' => $model]);
            }
        });
    }
}

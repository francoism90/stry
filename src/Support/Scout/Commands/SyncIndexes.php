<?php

declare(strict_types=1);

namespace Support\Scout\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SyncIndexes extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'scout:sync {--delete}';

    /**
     * @var string
     */
    protected $description = 'Sync Scout indexes';

    public function handle(): void
    {
        if ($this->option('delete')) {
            $this->call('scout:delete-all-indexes');
        }

        // Sync index settings
        $this->call('scout:sync-index-settings');

        // Sync index models
        $indexes = $this->getIndexes();

        if (count($indexes)) {
            foreach ($indexes as $model => $settings) {
                if (class_exists($model)) {
                    $this->call('scout:import', compact('model'));
                }
            }
        }

        $this->components->info('Indexes have been synced successfully.');
    }

    protected function getIndexes(): array
    {
        $driver = config('scout.driver');

        return (array) config('scout.'.$driver.'.index-settings', []);
    }
}

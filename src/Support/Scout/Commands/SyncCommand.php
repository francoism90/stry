<?php

declare(strict_types=1);

namespace Support\Scout\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;

class SyncCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'scout:sync';

    /**
     * @var string
     */
    protected $description = 'Sync Scout indexes';

    public function handle(): void
    {
        // Sync index models
        $indexes = $this->getIndexes();

        if (count($indexes)) {
            foreach ($indexes as $model => $settings) {
                if (class_exists($model)) {
                    info("Importing records for model: {$model}");

                    $this->call('scout:queue-import', compact('model'));
                }
            }
        }

        info('Indexes have been synced successfully.');
    }

    protected function getIndexes(): array
    {
        return (array) config('scout.typesense.model-settings', []);
    }
}

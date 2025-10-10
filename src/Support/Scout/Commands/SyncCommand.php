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
    protected $signature = 'scout:sync {--flush}';

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
            if ($this->option('flush')) {
                info("Flushing existing records for model: {$model}");

                $this->call('scout:flush', compact('model'));
            }

            info("Importing records for model: {$model}");

            $this->call('scout:queue-import', compact('model'));
        });
    }
}

<?php

declare(strict_types=1);

namespace Support\Scout\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\TypesenseEngine;
use Typesense\Client;
use Typesense\Exceptions\ObjectNotFound;

use function Laravel\Prompts\info;

class SyncScoutCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'scout:sync {--delete} {--import}';

    /**
     * @var string
     */
    protected $description = 'Sync Typesense indexes';

    public function handle(EngineManager $manager): void
    {
        $indexes = Config::collection('scout.typesense.model-settings');

        if ($indexes->isEmpty()) {
            info('No indexes configured to sync');
        }

        $engine = $manager->engine();

        $indexes->each(function (array $settings, string $model) use ($engine) {
            // Delete existing index
            if ($this->option('delete')) {
                $this->call('scout:delete-index', ['name' => $model]);
            }

            // Recreate index
            $this->call('scout:index', ['name' => $model]);

            // Typesense only creates collections on import, so empty ones would be missing for joins
            $instance = new $model;

            if ($engine instanceof TypesenseEngine && method_exists($instance, 'indexableAs')) {
                $this->createTypesenseCollection($instance->indexableAs(), $settings['collection-schema'] ?? []);
            }

            // Import records
            if ($this->option('import')) {
                $this->call('scout:import', ['model' => $model]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    protected function createTypesenseCollection(string $name, array $schema): void
    {
        $client = new Client(Config::array('scout.typesense.client-settings'));

        try {
            $client->getCollections()->{$name}->retrieve();
        } catch (ObjectNotFound) {
            $client->getCollections()->create([...$schema, 'name' => $name]);
        }
    }
}

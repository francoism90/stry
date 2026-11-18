<?php

declare(strict_types=1);

namespace Support\System\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

/**
 * Used by the idle check of the on-demand services (the `ondemand:` section of pods.yml),
 * which stops Horizon once the app sleeps and this command succeeds.
 */
class CheckIdleCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:idle';

    /**
     * @var string
     */
    protected $description = 'Succeed when no jobs or queries are in progress, so the idle check can stop Horizon';

    public function handle(): int
    {
        $work = [...$this->busyQueues(), ...$this->activeQueries()];

        foreach ($work as $message) {
            $this->line($message);
        }

        return $work === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Jobs that are waiting, running or delayed on the default queue, the
     * queues of Horizon's supervisors and Scout's queue.
     *
     * @return array<int, string>
     */
    protected function busyQueues(): array
    {
        return $this->watchedQueues()
            ->map(fn (array $queue): array => [...$queue, 'size' => Queue::connection($queue['connection'])->size($queue['queue'])])
            ->filter(fn (array $queue): bool => $queue['size'] > 0)
            ->map(fn (array $queue): string => "{$queue['size']} jobs on {$queue['connection']}:{$queue['queue']}")
            ->values()
            ->all();
    }

    /**
     * Queries and open transactions of other clients, such as a long
     * migration. Idle connections, like a worker waiting for a job, don't
     * count.
     *
     * @return array<int, string>
     */
    protected function activeQueries(): array
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'pgsql') {
            return [];
        }

        $active = (int) $connection->scalar("select count(*) from pg_stat_activity where datname = current_database() and pid <> pg_backend_pid() and backend_type = 'client backend' and state <> 'idle'");

        return $active === 0 ? [] : ["{$active} active queries on {$connection->getName()}"];
    }

    /**
     * @return Collection<int, array{connection: string, queue: string}>
     */
    protected function watchedQueues(): Collection
    {
        $default = Config::string('queue.default');

        $scout = Config::get('scout.queue');

        return Collection::make([
            ['connection' => $default, 'queue' => $this->defaultQueue($default)],
            ...$this->horizonQueues(),
            ...(is_array($scout) ? [[
                'connection' => (string) ($scout['connection'] ?? $default),
                'queue' => (string) ($scout['queue'] ?? $this->defaultQueue((string) ($scout['connection'] ?? $default))),
            ]] : []),
        ])
            ->reject(fn (array $queue): bool => in_array(Config::get("queue.connections.{$queue['connection']}.driver"), [null, 'sync', 'null'], true))
            ->unique(fn (array $queue): string => "{$queue['connection']}:{$queue['queue']}")
            ->values();
    }

    protected function defaultQueue(string $connection): string
    {
        return (string) Config::get("queue.connections.{$connection}.queue", 'default');
    }

    /**
     * @return array<int, array{connection: string, queue: string}>
     */
    protected function horizonQueues(): array
    {
        /** @var array<string, array<string, mixed>> $defaults */
        $defaults = Config::array('horizon.defaults', []);

        /** @var array<string, array<string, mixed>> $environment */
        $environment = Config::array('horizon.environments.'.app()->environment(), []);

        return Collection::make(array_replace_recursive($defaults, $environment))
            ->flatMap(fn (array $supervisor): array => Collection::make(Arr::wrap($supervisor['queue'] ?? 'default'))
                ->flatMap(fn (mixed $queue): array => explode(',', (string) $queue))
                ->map(fn (string $queue): string => trim($queue))
                ->filter()
                ->map(fn (string $queue): array => ['connection' => (string) ($supervisor['connection'] ?? 'redis'), 'queue' => $queue])
                ->all())
            ->values()
            ->all();
    }
}

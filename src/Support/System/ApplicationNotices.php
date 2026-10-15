<?php

declare(strict_types=1);

namespace Support\System;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Migrations\Migrator;
use Support\Filesystem\RegisterStorageDisks;

class ApplicationNotices
{
    public const string PendingMigrationsKey = 'application:pending-migrations';

    public function __construct(
        protected RegisterStorageDisks $disks,
        protected Migrator $migrator,
        protected Repository $cache,
    ) {}

    /**
     * Whether the storage disks changed after this worker registered them. Octane and queue
     * workers only register them when they boot, so they need a restart to use the new disks.
     */
    public function requiresRestart(): bool
    {
        return config('filesystems.fingerprint') !== $this->disks->fingerprint();
    }

    /**
     * Whether some database or settings migrations haven't run yet, such as after pulling a new
     * release. The result is cached until the migration files change or migrations have run.
     */
    public function hasPendingMigrations(): bool
    {
        $migrations = array_keys($this->migrator->getMigrationFiles([
            database_path('migrations'),
            ...$this->migrator->paths(),
        ]));

        $fingerprint = hash('xxh128', implode(',', $migrations));

        /** @var array{fingerprint: string, pending: bool}|null $cached */
        $cached = $this->cache->get(static::PendingMigrationsKey);

        if ($cached !== null && $cached['fingerprint'] === $fingerprint) {
            return $cached['pending'];
        }

        $pending = ! $this->migrator->repositoryExists()
            || array_diff($migrations, $this->migrator->getRepository()->getRan()) !== [];

        $this->cache->forever(static::PendingMigrationsKey, [
            'fingerprint' => $fingerprint,
            'pending' => $pending,
        ]);

        return $pending;
    }

    public function forgetPendingMigrations(): void
    {
        $this->cache->forget(static::PendingMigrationsKey);
    }

    /**
     * @return array{restart: bool, migrations: bool}
     */
    public function toArray(): array
    {
        return [
            'restart' => $this->requiresRestart(),
            'migrations' => $this->hasPendingMigrations(),
        ];
    }
}

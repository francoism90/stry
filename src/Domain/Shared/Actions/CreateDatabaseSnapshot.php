<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

use Foundation\Settings\SnapshotSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Spatie\DbSnapshots\Snapshot;
use Spatie\DbSnapshots\SnapshotFactory;
use Spatie\DbSnapshots\SnapshotRepository;

class CreateDatabaseSnapshot
{
    public function __construct(
        protected SnapshotFactory $snapshotFactory,
        protected SnapshotRepository $snapshotRepository,
        protected SnapshotSettings $settings,
    ) {}

    /**
     * Dump the database to the snapshots disk, then delete the snapshots beyond the ones to keep.
     */
    public function handle(): Snapshot
    {
        $snapshot = $this->snapshotFactory->create(
            snapshotName: Date::now()->format('Y-m-d_H-i-s'),
            diskName: Config::string('db-snapshots.disk'),
            connectionName: Config::get('db-snapshots.default_connection') ?? Config::string('database.default'),
            compress: Config::boolean('db-snapshots.compress'),
        );

        $this->snapshotRepository->getAll()
            ->splice($this->settings->keep)
            ->each(fn (Snapshot $snapshot) => $snapshot->delete());

        return $snapshot;
    }
}

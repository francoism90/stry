<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

use Foundation\Settings\SnapshotSettings;
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
            snapshotName: now()->format('Y-m-d_H-i-s'),
            diskName: config()->string('db-snapshots.disk'),
            connectionName: config('db-snapshots.default_connection') ?? config()->string('database.default'),
            compress: config()->boolean('db-snapshots.compress'),
        );

        $this->snapshotRepository->getAll()
            ->splice($this->settings->keep)
            ->each(fn (Snapshot $snapshot) => $snapshot->delete());

        return $snapshot;
    }
}

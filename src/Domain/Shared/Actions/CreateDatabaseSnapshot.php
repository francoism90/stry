<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

use Domain\Shared\Enums\DiskContent;
use Foundation\Settings\SnapshotSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Spatie\DbSnapshots\Snapshot;
use Spatie\DbSnapshots\SnapshotFactory;
use Spatie\DbSnapshots\SnapshotRepository;

class CreateDatabaseSnapshot
{
    public function __construct(
        protected SnapshotFactory $snapshotFactory,
        protected SnapshotSettings $settings,
    ) {}

    /**
     * Dump the database to the default disk for snapshots, then delete the snapshots beyond the
     * ones to keep.
     */
    public function handle(): Snapshot
    {
        $disk = DiskContent::Snapshots->disk();

        $snapshot = $this->snapshotFactory->create(
            snapshotName: Date::now()->format('Y-m-d_H-i-s'),
            diskName: $disk,
            connectionName: Config::get('db-snapshots.default_connection') ?? Config::string('database.default'),
            compress: $this->settings->compress,
        );

        new SnapshotRepository(Storage::disk($disk))->getAll()
            ->splice($this->settings->keep)
            ->each(fn (Snapshot $snapshot) => $snapshot->delete());

        return $snapshot;
    }
}

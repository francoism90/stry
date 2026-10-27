<?php

declare(strict_types=1);

use Domain\Shared\DataObjects\StorageDisk;
use Foundation\Settings\SnapshotSettings;
use Foundation\Settings\StorageSettings;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\DbSnapshots\Snapshot;
use Spatie\DbSnapshots\SnapshotFactory;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->root = storage_path('framework/testing/disks');

    File::ensureDirectoryExists("{$this->root}/archive");

    $settings = app(StorageSettings::class);
    $settings->disks = [
        StorageDisk::from([
            'name' => 'archive',
            'label' => 'Archive',
            'driver' => 'local',
            'path' => "{$this->root}/archive",
            'visibility' => 'private',
            'serve' => false,
            'contents' => ['snapshots'],
            'default_for' => ['snapshots'],
        ]),
    ];
    $settings->save();
});

afterEach(fn () => File::deleteDirectory($this->root));

it('creates a snapshot and deletes the ones beyond the number to keep', function () {
    $disk = Storage::disk('archive');

    foreach (['2026-10-24', '2026-10-25', '2026-10-26'] as $day) {
        $disk->put("{$day}_04-30-00.sql.gz", 'dump');
        touch($disk->path("{$day}_04-30-00.sql.gz"), strtotime("{$day} 04:30:00"));
    }

    $settings = app(SnapshotSettings::class);
    $settings->keep = 2;
    $settings->save();

    $this->mock(SnapshotFactory::class)
        ->shouldReceive('create')
        ->once()
        ->andReturnUsing(function () use ($disk): Snapshot {
            $disk->put('2026-10-27_04-30-00.sql.gz', 'dump');

            return new Snapshot($disk, '2026-10-27_04-30-00.sql.gz');
        });

    artisan('app:snapshot')->assertSuccessful();

    expect($disk->files())->toEqualCanonicalizing([
        '2026-10-26_04-30-00.sql.gz',
        '2026-10-27_04-30-00.sql.gz',
    ]);
});

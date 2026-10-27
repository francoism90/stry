<?php

declare(strict_types=1);

use Domain\Shared\DataObjects\StorageCredentials;
use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Foundation\Settings\StorageSettings;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->root = storage_path('framework/testing/disks');

    File::ensureDirectoryExists("{$this->root}/exports");
});

afterEach(fn () => File::deleteDirectory($this->root));

function migrateSnapshotsDisk(): StorageSettings
{
    (require database_path('settings/2026_10_27_140000_add_snapshots_disk_to_storage_settings.php'))->up();

    return app(StorageSettings::class)->refresh();
}

it('adds a snapshots disk in the snapshots folder of a local exports disk', function () {
    $settings = app(StorageSettings::class);
    $settings->disks = [StorageDisk::from([
        'name' => 'exports',
        'label' => 'Exports',
        'driver' => 'local',
        'path' => "{$this->root}/exports",
        'contents' => ['exports'],
        'default_for' => ['exports'],
    ])];
    $settings->save();

    $disk = migrateSnapshotsDisk()->defaultDiskFor(DiskContent::Snapshots);

    expect($disk?->name)->toBe('snapshots')
        ->and($disk?->path)->toBe("{$this->root}/exports/snapshots")
        ->and($disk?->contents)->toBe(['snapshots'])
        ->and("{$this->root}/exports/snapshots")->toBeDirectory();
});

it('passes the credentials of an S3 exports disk on to the snapshots disk', function () {
    $settings = app(StorageSettings::class);
    $settings->disks = [StorageDisk::from([
        'name' => 'archive',
        'label' => 'Archive',
        'driver' => 's3',
        'path' => 'backups',
        'bucket' => 'archive',
        'contents' => ['exports'],
        'default_for' => ['exports'],
    ])];
    $settings->credentials = ['archive' => new StorageCredentials(key: 'AKIAEXAMPLEKEY12', secret: 'top-secret')];
    $settings->save();

    $settings = migrateSnapshotsDisk();

    expect($settings->find('snapshots')?->path)->toBe('backups/snapshots')
        ->and($settings->find('snapshots')?->bucket)->toBe('archive')
        ->and($settings->credentials['snapshots']->key ?? null)->toBe('AKIAEXAMPLEKEY12')
        ->and($settings->credentials['snapshots']->secret ?? null)->toBe('top-secret');
});

it('leaves an existing snapshots disk alone', function () {
    $settings = app(StorageSettings::class);
    $disks = $settings->disks;

    expect(migrateSnapshotsDisk()->disks)->toEqual($disks);
});

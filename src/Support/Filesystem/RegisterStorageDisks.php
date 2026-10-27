<?php

declare(strict_types=1);

namespace Support\Filesystem;

use Domain\Shared\Enums\DiskContent;
use Foundation\Settings\StorageSettings;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\FilesystemManager;

class RegisterStorageDisks
{
    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Add the disks of the storage settings to "filesystems.disks", and remove the ones that were
     * deleted. Their names are kept in "filesystems.managed". Disks that were already resolved and
     * have changed are forgotten, so they're built again from their new configuration.
     */
    public function handle(): void
    {
        $disks = $this->disks();

        if ($disks === null) {
            return;
        }

        /** @var Repository $config */
        $config = $this->container->make('config');

        /** @var FilesystemManager $filesystem */
        $filesystem = $this->container->make('filesystem');

        foreach (array_diff((array) $config->get('filesystems.managed', []), array_keys($disks)) as $name) {
            $config->set("filesystems.disks.{$name}", null);
            $filesystem->forgetDisk($name);
        }

        foreach ($disks as $name => $disk) {
            if ($config->get("filesystems.disks.{$name}") === $disk) {
                continue;
            }

            $config->set("filesystems.disks.{$name}", $disk);
            $filesystem->forgetDisk($name);
        }

        $config->set('filesystems.managed', array_keys($disks));

        $this->registerSnapshotDisk($config, $filesystem);
    }

    /**
     * Database snapshots are kept in the "snapshots" folder of the disk that holds exports, as a
     * copy of that disk with the folder added to its root.
     */
    protected function registerSnapshotDisk(Repository $config, FilesystemManager $filesystem): void
    {
        $name = (string) $config->get('db-snapshots.disk');

        $exports = (array) $config->get('filesystems.disks.'.DiskContent::Exports->disk(), []);

        $root = rtrim((string) ($exports['root'] ?? ''), '/');

        $disk = $exports === [] ? null : [
            ...$exports,
            'root' => $root === '' ? 'snapshots' : "{$root}/snapshots",
        ];

        if ($config->get("filesystems.disks.{$name}") === $disk) {
            return;
        }

        $config->set("filesystems.disks.{$name}", $disk);
        $filesystem->forgetDisk($name);
    }

    /**
     * A hash of the disks in the storage settings, to tell whether they changed after the disks
     * were registered. It's null when the settings can't be read, such as before migrating.
     */
    public function fingerprint(): ?string
    {
        $disks = $this->disks();

        return $disks === null ? null : hash('xxh128', serialize($disks));
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    protected function disks(): ?array
    {
        return rescue(
            fn (): array => $this->container->make(StorageSettings::class)->toFilesystemConfig(),
            report: false,
        );
    }
}

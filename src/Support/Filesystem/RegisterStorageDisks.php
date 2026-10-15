<?php

declare(strict_types=1);

namespace Support\Filesystem;

use Foundation\Settings\StorageSettings;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

class RegisterStorageDisks
{
    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Add the disks of the storage settings to "filesystems.disks", and remove the ones that were
     * deleted. Their names are kept in "filesystems.managed". Disks already resolved are rebuilt by
     * the filesystem manager once their configuration changes.
     */
    public function handle(): void
    {
        $disks = rescue(
            fn (): array => $this->container->make(StorageSettings::class)->toFilesystemConfig(),
            report: false,
        );

        if ($disks === null) {
            return;
        }

        /** @var Repository $config */
        $config = $this->container->make('config');

        foreach (array_diff((array) $config->get('filesystems.managed', []), array_keys($disks)) as $name) {
            $config->set("filesystems.disks.{$name}", null);
        }

        foreach ($disks as $name => $disk) {
            $config->set("filesystems.disks.{$name}", $disk);
        }

        $config->set('filesystems.managed', array_keys($disks));
    }
}

<?php

declare(strict_types=1);

namespace Support\Filesystem;

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
        $disks = rescue(
            fn (): array => $this->container->make(StorageSettings::class)->toFilesystemConfig(),
            report: false,
        );

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
    }
}

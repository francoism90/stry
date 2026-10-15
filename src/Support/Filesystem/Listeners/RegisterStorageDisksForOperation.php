<?php

declare(strict_types=1);

namespace Support\Filesystem\Listeners;

use Support\Filesystem\RegisterStorageDisks;

/**
 * Registers the storage disks again for each Octane request, which gets a fresh copy of the
 * configuration, so disks saved by another worker are picked up.
 */
class RegisterStorageDisksForOperation
{
    public function handle(object $event): void
    {
        $event->sandbox->make(RegisterStorageDisks::class)->handle();
    }
}

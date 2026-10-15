<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

use Domain\Shared\DataObjects\StorageCredentials;
use Domain\Shared\DataObjects\StorageDisk;
use Foundation\Settings\StorageSettings;

class UpdateStorageSettings
{
    /**
     * @param  array<int, StorageDisk>  $disks
     * @param  array<string, StorageCredentials>  $credentials
     */
    public function handle(array $disks, array $credentials): StorageSettings
    {
        $settings = app(StorageSettings::class);

        $settings->disks = $disks;
        $settings->credentials = $credentials;

        return $settings->save();
    }
}

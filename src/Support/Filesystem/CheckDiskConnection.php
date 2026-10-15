<?php

declare(strict_types=1);

namespace Support\Filesystem;

use Illuminate\Support\Facades\Storage;
use Throwable;

class CheckDiskConnection
{
    /**
     * Whether a disk with the given configuration can be reached, by looking for its root.
     *
     * @param  array<string, mixed>  $config
     */
    public function handle(array $config): bool
    {
        try {
            Storage::build([...$config, 'throw' => true])->directoryExists('');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

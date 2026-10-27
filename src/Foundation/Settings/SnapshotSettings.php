<?php

declare(strict_types=1);

namespace Foundation\Settings;

use Domain\Shared\Enums\SnapshotFrequency;
use Spatie\LaravelSettings\Settings;

class SnapshotSettings extends Settings
{
    public SnapshotFrequency $frequency = SnapshotFrequency::Never;

    /**
     * How many snapshots are kept; older ones are deleted after a new one is created.
     */
    public int $keep = 7;

    public bool $compress = true;

    public static function group(): string
    {
        return 'snapshots';
    }
}

<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Foundation\Settings\SnapshotSettings;
use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum SnapshotFrequency: string implements Enumerable
{
    use HasLabels;

    case Never = 'never';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Never => __('Never'),
            self::Daily => __('Daily'),
            self::Weekly => __('Weekly'),
            self::Monthly => __('Monthly'),
        };
    }

    /**
     * The frequency in the snapshot settings, or never when the settings can't be read.
     */
    public static function configured(): self
    {
        return rescue(
            fn (): self => app(SnapshotSettings::class)->frequency,
            report: false,
        ) ?? self::Never;
    }

    /**
     * The cron expression snapshots are created on, at night after the other maintenance tasks.
     */
    public function expression(): ?string
    {
        return match ($this) {
            self::Never => null,
            self::Daily => '0 3 * * *',
            self::Weekly => '0 3 * * 1',
            self::Monthly => '0 3 1 * *',
        };
    }
}

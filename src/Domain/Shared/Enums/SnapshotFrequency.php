<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Domain\Shared\Contracts\Enumerable;
use Foundation\Settings\SnapshotSettings;

enum SnapshotFrequency: string implements Enumerable
{
    case Never = 'never';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'never' => __('Never'),
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
        ];
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
            self::Daily => '30 4 * * *',
            self::Weekly => '30 4 * * 1',
            self::Monthly => '30 4 1 * *',
        };
    }
}

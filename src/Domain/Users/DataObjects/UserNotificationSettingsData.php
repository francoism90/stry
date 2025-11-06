<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

/**
 * @phpstan-type SettingsPayload array<string, mixed>
 */
final class UserNotificationSettingsData extends Data
{
    /**
     * @param  SettingsPayload  $channels
     */
    public function __construct(
        public readonly array $channels = [],
    ) {}
}

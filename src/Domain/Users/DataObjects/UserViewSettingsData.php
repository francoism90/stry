<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

/**
 * @phpstan-type SettingsPayload array<string, mixed>
 */
final class UserViewSettingsData extends Data
{
    /**
     * @param  SettingsPayload  $settings
     */
    public function __construct(
        public readonly array $settings = [],
    ) {}
}

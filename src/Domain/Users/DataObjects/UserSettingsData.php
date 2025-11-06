<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

final class UserSettingsData extends Data
{
    public function __construct(
        public readonly ?UserViewSettingsData $view = null,
        public readonly ?UserNotificationSettingsData $notifications = null,
    ) {}
}

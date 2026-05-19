<?php

declare(strict_types=1);

namespace Domain\Users\Actions;

use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;

class UpdateUserSettings
{
    public function handle(User $user, array $settings = []): void
    {
        $currentSettings = $user->getSettings()->toArray();

        $mergedSettings = array_replace_recursive($currentSettings, $settings);

        $user->settings = UserSettings::from($mergedSettings);
        $user->saveOrFail();
    }
}

<?php

declare(strict_types=1);

namespace Domain\Users\Actions;

use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;

class UpdateUserSettings
{
    public function handle(User $user, array $settings = []): void
    {
        // Get current settings
        $currentSettings = UserSettings::fromModel($user)->include('*')->toArray();

        // Merge with new settings
        $mergedSettings = array_replace_recursive($currentSettings, $settings);

        // Update user settings
        $user->settings = UserSettings::from($mergedSettings);
        $user->saveOrFail();
    }
}

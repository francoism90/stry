<?php

declare(strict_types=1);

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserAppearanceSettings;
use Domain\Users\DataObjects\UserGeneralSettings;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;

it('merges user settings and persists the changes', function (): void {
    $user = User::factory()->create([
        'settings' => [
            'general' => [
                'timezone' => 'UTC',
                'locale' => 'en_US',
                'language' => 'en',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            ],
            'appearance' => [
                'theme' => 'dark',
                'grid_view' => true,
            ],
        ],
    ]);

    $action = new UpdateUserSettings;

    $action->handle($user, [
        'general' => [
            'timezone' => 'Europe/Amsterdam',
        ],
        'appearance' => [
            'theme' => 'light',
        ],
    ]);

    $user->refresh();

    $settings = UserSettings::from($user->settings);

    expect($settings->general)
        ->toBeInstanceOf(UserGeneralSettings::class);

    expect($settings->general->timezone)->toBe('Europe/Amsterdam');
    expect($settings->general->locale)->toBe('en_US');

    expect($settings->appearance)
        ->toBeInstanceOf(UserAppearanceSettings::class);

    expect($settings->appearance->theme)->toBe('light');
    expect($settings->appearance->grid_view)->toBeTrue();
});

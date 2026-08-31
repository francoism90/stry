<?php

declare(strict_types=1);

use Domain\Shared\Enums\Locale;
use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\AppearanceSettings;
use Domain\Users\DataObjects\GeneralSettings;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;

it('merges user settings and persists the changes', function (): void {
    $user = User::factory()->create([
        'settings' => [
            'general' => [
                'timezone' => 'UTC',
                'locale' => 'en-US',
                'language' => 'en',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
            ],
            'appearance' => [
                'theme' => 'dark',
                'default_view' => 'vertical',
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
        ->toBeInstanceOf(GeneralSettings::class);

    expect($settings->general->timezone)->toBe('Europe/Amsterdam');
    expect($settings->general->locale)->toBe(Locale::EnUs);

    expect($settings->appearance)
        ->toBeInstanceOf(AppearanceSettings::class);

    expect($settings->appearance->theme)->toBe('light');
    expect($settings->appearance->default_view)->toBe('vertical');
});

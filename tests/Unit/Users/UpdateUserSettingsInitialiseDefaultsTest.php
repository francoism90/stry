<?php

declare(strict_types=1);

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\AppearanceSettings;
use Domain\Users\DataObjects\GeneralSettings;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;

it('initialises default settings when user settings are null', function (): void {
    $user = User::factory()->create([
        'settings' => null,
    ]);

    $action = new UpdateUserSettings;

    $action->handle($user, [
        'general' => [
            'timezone' => 'Europe/Paris',
        ],
    ]);

    $user->refresh();

    $settings = UserSettings::from($user->settings);

    expect($settings->general)
        ->toBeInstanceOf(GeneralSettings::class);

    expect($settings->general->timezone)->toBe('Europe/Paris');
    expect($settings->general->locale)->toBe('en-US');

    expect($settings->appearance)
        ->toBeInstanceOf(AppearanceSettings::class);

    expect($settings->appearance->theme)->toBe('dark');
});

<?php

declare(strict_types=1);

use App\Web\Settings\Controllers\ApplicationSettingsController;
use Domain\Users\Models\User;
use Foundation\Settings\GeneralSettings;

it('allows a super-admin to update application settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action(ApplicationSettingsController::class), [
        'site_name' => 'Stry',
        'allow_registration' => true,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(GeneralSettings::class);

    expect($settings->site_name)->toBe('Stry')
        ->and($settings->allow_registration)->toBeTrue()
        ->and($settings->timezone)->toBe('Europe/Amsterdam');
});

it('denies a regular user from updating application settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action(ApplicationSettingsController::class), [
        'site_name' => 'Stry',
    ]);

    $response->assertForbidden();
});

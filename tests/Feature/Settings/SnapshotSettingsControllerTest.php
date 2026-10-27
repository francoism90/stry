<?php

declare(strict_types=1);

use Domain\Shared\Enums\SnapshotFrequency;
use Domain\Users\Models\User;
use Foundation\Settings\SnapshotSettings;
use Modules\Web\Settings\Controllers\SnapshotSettingsController;

it('allows a super-admin to fetch snapshot settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([SnapshotSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertExactJson(['frequency' => 'never', 'keep' => 7, 'compress' => true]);
});

it('denies a regular user from fetching snapshot settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([SnapshotSettingsController::class, 'show']));

    $response->assertForbidden();
});

it('allows a super-admin to update snapshot settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([SnapshotSettingsController::class, 'update']), [
        'frequency' => 'weekly',
        'keep' => 4,
        'compress' => false,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(SnapshotSettings::class);

    expect($settings->frequency)->toBe(SnapshotFrequency::Weekly)
        ->and($settings->keep)->toBe(4)
        ->and($settings->compress)->toBeFalse();
});

it('denies a regular user from updating snapshot settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action([SnapshotSettingsController::class, 'update']), [
        'frequency' => 'daily',
    ]);

    $response->assertForbidden();
});

it('rejects an unknown frequency', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([SnapshotSettingsController::class, 'update']), [
        'frequency' => 'hourly',
    ]);

    $response->assertInvalid(['frequency']);
});

it('rejects keeping fewer than one snapshot', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([SnapshotSettingsController::class, 'update']), [
        'keep' => 0,
    ]);

    $response->assertInvalid(['keep']);
});

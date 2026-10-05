<?php

declare(strict_types=1);

use Domain\Shared\Enums\Language;
use Domain\Users\Models\User;
use Domain\Videos\Settings\PlaybackSettings;
use Modules\Web\Settings\Controllers\PlaybackSettingsController;

it('allows a super-admin to fetch playback settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([PlaybackSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertExactJson([
        'text_language' => Language::English->value,
        'encryption' => false,
        'refresh_before' => 300,
        'renditions' => [],
        'rendition_options' => [
            ['value' => 1080, 'label' => '1080p'],
            ['value' => 720, 'label' => '720p'],
            ['value' => 480, 'label' => '480p'],
            ['value' => 360, 'label' => '360p'],
        ],
    ]);
});

it('denies a regular user from fetching playback settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([PlaybackSettingsController::class, 'show']));

    $response->assertForbidden();
});

it('allows a super-admin to update playback settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([PlaybackSettingsController::class, 'update']), [
        'text_language' => 'nl',
        'encryption' => true,
        'refresh_before' => 120,
        'renditions' => [480, '720'],
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(PlaybackSettings::class);

    expect($settings->text_language)->toBe(Language::Dutch)
        ->and($settings->encryption)->toBeTrue()
        ->and($settings->refresh_before)->toBe(120)
        ->and($settings->renditions)->toBe([720, 480]);
});

it('denies a regular user from updating playback settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action([PlaybackSettingsController::class, 'update']), [
        'encryption' => true,
    ]);

    $response->assertForbidden();
});

it('rejects an invalid text language', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([PlaybackSettingsController::class, 'update']), [
        'text_language' => 'invalid',
    ]);

    $response->assertInvalid(['text_language']);
});

it('rejects renditions of other sizes', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([PlaybackSettingsController::class, 'update']), [
        'renditions' => [540],
    ]);

    $response->assertInvalid(['renditions.0']);
});

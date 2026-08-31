<?php

declare(strict_types=1);

use App\Web\Settings\Controllers\PlaylistSettingsController;
use Domain\Playlists\Enums\EncryptionMethod;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Enums\ProtectionScheme;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Shared\Enums\Language;
use Domain\Users\Models\User;

it('allows a super-admin to fetch playlist settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([PlaylistSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertJson([
        ...app(PlaylistSettings::class)->toArray(),
        'type' => PlaylistType::Packager->value,
        'language' => Language::English->value,
        'text_language' => Language::English->value,
    ]);
});

it('denies a regular user from fetching playlist settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([PlaylistSettingsController::class, 'show']));

    $response->assertForbidden();
});

it('allows a super-admin to update playlist settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([PlaylistSettingsController::class, 'update']), [
        'type' => 'streamer',
        'encryption' => 'clearkey',
        'protection_scheme' => 'cenc',
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(PlaylistSettings::class);

    expect($settings->type)->toBe(PlaylistType::Streamer)
        ->and($settings->encryption)->toBe(EncryptionMethod::ClearKey)
        ->and($settings->protection_scheme)->toBe(ProtectionScheme::Cenc)
        ->and($settings->disk_name)->toBe('segments');
});

it('denies a regular user from updating playlist settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action([PlaylistSettingsController::class, 'update']), [
        'type' => 'streamer',
    ]);

    $response->assertForbidden();
});

it('rejects an invalid encryption value', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([PlaylistSettingsController::class, 'update']), [
        'encryption' => 'invalid-method',
    ]);

    $response->assertInvalid(['encryption']);
});

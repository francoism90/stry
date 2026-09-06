<?php

declare(strict_types=1);

use App\Web\Settings\Controllers\ChapterSettingsController;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Settings\ChapterSettings;
use Domain\Users\Models\User;

it('allows a super-admin to fetch chapter settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([ChapterSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertJson([
        ...app(ChapterSettings::class)->toArray(),
        'default_type' => ChapterType::Scene->value,
    ]);
});

it('denies a regular user from fetching chapter settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([ChapterSettingsController::class, 'show']));

    $response->assertForbidden();
});

it('allows a super-admin to update chapter settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ChapterSettingsController::class, 'update']), [
        'default_type' => 'credits',
        'patterns' => json_encode(['intro' => '/foo/i']),
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(ChapterSettings::class);

    expect($settings->default_type)->toBe(ChapterType::Credits)
        ->and($settings->patterns)->toBe(['intro' => '/foo/i']);
});

it('denies a regular user from updating chapter settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action([ChapterSettingsController::class, 'update']), [
        'default_type' => 'credits',
    ]);

    $response->assertForbidden();
});

it('rejects an invalid default type', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ChapterSettingsController::class, 'update']), [
        'default_type' => 'invalid-type',
    ]);

    $response->assertInvalid(['default_type']);
});

it('rejects patterns that are not valid json', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ChapterSettingsController::class, 'update']), [
        'patterns' => 'not-json',
    ]);

    $response->assertInvalid(['patterns']);
});

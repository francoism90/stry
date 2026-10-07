<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Settings\ProcessingSettings;
use Modules\Web\Settings\Controllers\ProcessingSettingsController;

it('allows a super-admin to fetch processing settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([ProcessingSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertJson(app(ProcessingSettings::class)->toArray());
});

it('denies a regular user from fetching processing settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([ProcessingSettingsController::class, 'show']));

    $response->assertForbidden();
});

it('allows a super-admin to update processing settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ProcessingSettingsController::class, 'update']), [
        'extract_captions' => false,
        'extract_chapters' => false,
        'extract_storyboard' => false,
        'create_renditions' => true,
        'create_reels' => true,
        'reel_width' => 720,
        'reel_height' => 1280,
        'reel_fps' => 24,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $settings = app(ProcessingSettings::class);

    expect($settings->extract_captions)->toBeFalse()
        ->and($settings->extract_chapters)->toBeFalse()
        ->and($settings->extract_storyboard)->toBeFalse()
        ->and($settings->create_renditions)->toBeTrue()
        ->and($settings->create_reels)->toBeTrue()
        ->and([$settings->reel_width, $settings->reel_height, $settings->reel_fps])->toBe([720, 1280, 24]);
});

it('denies a regular user from updating processing settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action([ProcessingSettingsController::class, 'update']), [
        'extract_captions' => false,
    ]);

    $response->assertForbidden();
});

it('rejects a non-boolean value', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ProcessingSettingsController::class, 'update']), [
        'extract_captions' => 'not-a-boolean',
    ]);

    $response->assertInvalid(['extract_captions']);
});

it('rejects reel sizes ffmpeg cannot encode', function (array $values, string $field) {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ProcessingSettingsController::class, 'update']), $values);

    $response->assertInvalid([$field]);
})->with([
    'odd width' => [['reel_width' => 1081], 'reel_width'],
    'too small height' => [['reel_height' => 100], 'reel_height'],
    'too high frame rate' => [['reel_fps' => 120], 'reel_fps'],
]);

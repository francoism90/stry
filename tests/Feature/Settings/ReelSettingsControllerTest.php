<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Settings\ReelSettings;
use Modules\Web\Settings\Controllers\ReelSettingsController;

it('allows a super-admin to fetch reel settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([ReelSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertJson(app(ReelSettings::class)->toArray());
});

it('allows a super-admin to update reel settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ReelSettingsController::class, 'update']), [
        'enabled' => true,
        'width' => 720,
        'height' => 1280,
        'fps' => 24,
        'fit' => 'letterbox',
        'zoom' => 70,
        'codec' => 'hevc',
        'hardware' => false,
        'quality' => 28,
        'cuts' => 5,
        'cut_duration' => 2.5,
        'duration' => 20,
        'scene_threshold' => 0.4,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    expect(app(ReelSettings::class)->toArray())->toMatchArray([
        'enabled' => true,
        'width' => 720,
        'height' => 1280,
        'fps' => 24,
        'fit' => 'letterbox',
        'zoom' => 70,
        'codec' => 'hevc',
        'hardware' => false,
        'quality' => 28,
        'cuts' => 5,
        'cut_duration' => 2.5,
        'duration' => 20,
        'scene_threshold' => 0.4,
    ]);
});

it('denies a regular user from updating reel settings', function () {
    $response = $this->actingAs(User::factory()->create())->patch(action([ReelSettingsController::class, 'update']), [
        'enabled' => true,
    ]);

    $response->assertForbidden();
});

it('rejects reel settings ffmpeg cannot use', function (array $values, string $field) {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->patch(action([ReelSettingsController::class, 'update']), $values);

    $response->assertInvalid([$field]);
})->with([
    'odd width' => [['width' => 1081], 'width'],
    'too small height' => [['height' => 100], 'height'],
    'too high frame rate' => [['fps' => 120], 'fps'],
    'quality out of range' => [['quality' => 60], 'quality'],
    'unknown codec' => [['codec' => 'mpeg2'], 'codec'],
    'unknown fit' => [['fit' => 'blur'], 'fit'],
    'zoom out of range' => [['zoom' => 150], 'zoom'],
    'cuts shorter than the minimum' => [['cut_duration' => 1], 'cut_duration'],
    'scene threshold out of range' => [['scene_threshold' => 1.5], 'scene_threshold'],
]);

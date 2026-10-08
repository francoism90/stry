<?php

declare(strict_types=1);

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\Models\User;
use Modules\Web\Users\Controllers\UserSettingsController;

it('flashes a success notification after updating settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action(UserSettingsController::class), [
        'general' => ['timezone' => 'UTC'],
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');
});

it('keeps the other player settings when updating one of them', function () {
    $user = User::factory()->create();

    (new UpdateUserSettings)->handle($user, [
        'player' => ['autoplay' => false, 'playback_speed' => 1.5, 'volume' => 1.0],
    ]);

    $this->actingAs($user)->patch(action(UserSettingsController::class), [
        'player' => ['volume' => 0.5],
    ]);

    $player = $user->refresh()->getSettings()->player;

    expect($player->volume)->toBe(0.5)
        ->and($player->autoplay)->toBeFalse()
        ->and($player->playback_speed)->toBe(1.5);
});

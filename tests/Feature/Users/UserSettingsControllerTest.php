<?php

declare(strict_types=1);

use App\Web\Users\Controllers\UserSettingsController;
use Domain\Users\Models\User;

it('flashes a success notification after updating settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(action(UserSettingsController::class), [
        'general' => ['timezone' => 'UTC'],
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');
});

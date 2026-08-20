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

it('includes the flash data in the redirect-follow Inertia response', function () {
    $user = User::factory()->create();

    $redirect = $this->actingAs($user)->patch(action(UserSettingsController::class), [
        'general' => ['timezone' => 'UTC'],
    ]);

    $redirect->assertRedirect();

    $follow = $this->actingAs($user)
        ->withHeaders(['X-Inertia' => 'true'])
        ->get($redirect->headers->get('Location'));

    $follow->assertJsonPath('flash.type', 'success');
});

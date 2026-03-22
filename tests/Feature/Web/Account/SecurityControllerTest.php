<?php

declare(strict_types=1);

use App\Web\Account\Controllers\SecurityController;
use Domain\Users\Models\User;

it('renders the security settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SecurityController::class));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('App/Account/SecuritySettings'));
});

it('redirects guests from the security settings page', function () {
    $response = $this->get(action(SecurityController::class));

    $response->assertRedirect();
});

it('allows an authenticated user to update their password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertRedirect();
});

it('rejects an incorrect current password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertInvalid(['current_password'], 'updatePassword');
});

it('rejects a missing password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'password',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertInvalid(['password'], 'updatePassword');
});

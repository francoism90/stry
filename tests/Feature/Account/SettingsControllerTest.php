<?php

declare(strict_types=1);

use App\Web\Account\Controllers\SettingsController;
use Domain\Users\Models\User;

it('renders the settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SettingsController::class));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Account/AccountSettings'));
});

it('redirects guests from the settings page', function () {
    $response = $this->get(action(SettingsController::class));

    $response->assertRedirect();
});

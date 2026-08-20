<?php

declare(strict_types=1);

use App\Web\Account\Controllers\AppearanceController;
use Domain\Users\Models\User;

it('renders the appearance settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(AppearanceController::class));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Account/AccountAppearance'));
});

it('redirects guests from the appearance settings page', function () {
    $response = $this->get(action(AppearanceController::class));

    $response->assertRedirect();
});

<?php

declare(strict_types=1);

use App\Web\Account\Controllers\AccountController;
use Domain\Users\Models\User;

it('renders the account page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(AccountController::class));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('App/Account/AccountIndex'));
});

it('redirects guests from the account page', function () {
    $response = $this->get(action(AccountController::class));

    $response->assertRedirect();
});

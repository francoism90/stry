<?php

declare(strict_types=1);

use App\Web\Home\Controllers\HomeController;
use Domain\Users\Models\User;

it('allows authenticated users to view the home page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(HomeController::class));

    $response->assertSuccessful();
});

it('redirects guests from viewing the home page', function () {
    $response = $this->get(action(HomeController::class));

    $response->assertRedirect();
});

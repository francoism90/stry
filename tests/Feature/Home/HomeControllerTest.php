<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Modules\Web\Home\Controllers\HomeController;

it('allows authenticated users to view the home page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(HomeController::class));

    $response->assertSuccessful();
});

it('redirects guests from viewing the home page', function () {
    $response = $this->get(action(HomeController::class));

    $response->assertRedirect();
});

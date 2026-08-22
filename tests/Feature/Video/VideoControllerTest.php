<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoController;
use Domain\Users\Models\User;

// index

it('allows super-admins to view the video library', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([VideoController::class, 'index']));

    $response->assertSuccessful();
});

it('redirects guests from viewing the video library', function () {
    $response = $this->get(action([VideoController::class, 'index']));

    $response->assertRedirect();
});

it('forbids regular users from viewing the video library', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'index']));

    $response->assertForbidden();
});

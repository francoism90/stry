<?php

declare(strict_types=1);

use Domain\Users\Models\User;

it('shares validation errors with the next inertia response', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this
        ->from(route('login'))
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertInertia(fn ($page) => $page->has('errors.email'));
});

<?php

declare(strict_types=1);

use App\Web\Users\Controllers\UserController;
use Domain\Users\Models\User;

// index

it('allows admins to view the user index', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([UserController::class, 'index']));

    $response->assertSuccessful();
});

it('redirects guests from viewing the user index', function () {
    $response = $this->get(action([UserController::class, 'index']));

    $response->assertRedirect();
});

it('forbids regular users from viewing the user index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([UserController::class, 'index']));

    $response->assertForbidden();
});

// update

it('allows admins to update another user', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $other = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->put(action([UserController::class, 'update'], $other), [
        'name' => 'New Name',
        'email' => $other->email,
    ]);

    $response->assertRedirect();
    expect($other->fresh()->name)->toBe('New Name');
});

it('resets verification when an admin changes another user email', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $other = User::factory()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($user)->put(action([UserController::class, 'update'], $other), [
        'name' => $other->name,
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect();
    expect($other->fresh())
        ->email->toBe('new@example.com')
        ->email_verified_at->toBeNull();
});

it('forbids regular users from updating another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->put(action([UserController::class, 'update'], $other), [
        'name' => 'New Name',
        'email' => $other->email,
    ]);

    $response->assertForbidden();
    expect($other->fresh()->name)->toBe('Old Name');
});

// destroy

it('allows admins to delete another user', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $other = User::factory()->create();

    $response = $this->actingAs($user)->delete(action([UserController::class, 'destroy'], $other));

    $response->assertRedirect();
    expect($other->fresh()->trashed())->toBeTrue();
});

it('forbids admins from deleting their own account', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->delete(action([UserController::class, 'destroy'], $user));

    $response->assertForbidden();
    expect($user->fresh()->trashed())->toBeFalse();
});

it('forbids regular users from deleting a user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $response = $this->actingAs($user)->delete(action([UserController::class, 'destroy'], $other));

    $response->assertForbidden();
    expect($other->fresh()->trashed())->toBeFalse();
});

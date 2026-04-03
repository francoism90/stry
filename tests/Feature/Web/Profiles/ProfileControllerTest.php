<?php

declare(strict_types=1);

use App\Web\Profiles\Controllers\ProfileController;
use App\Web\Profiles\Controllers\SwitchProfileController;
use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;

it('renders the profiles page for authenticated users', function () {
    $user = User::factory()->create();

    Profile::factory()->count(2)->create([
        'user_id' => $user->getKey(),
    ]);

    $response = $this->actingAs($user)->get(action([ProfileController::class, 'index']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('App/Profiles/ProfileIndex')
        ->has('profiles', 2)
    );
});

it('redirects guests from the profiles page', function () {
    $response = $this->get(action([ProfileController::class, 'index']));

    $response->assertRedirect();
});

it('switches the current profile for the authenticated user', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    $response = $this->actingAs($user)->post(action(SwitchProfileController::class, $profile));

    $response->assertRedirect();
    $response->assertSessionHas('profiles.current', $profile->getRouteKey());
});

it('marks the switched profile as current', function () {
    $user = User::factory()->create();

    $first = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'A',
    ]);

    $second = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'B',
    ]);

    $this->actingAs($user)->post(action(SwitchProfileController::class, $second));

    $response = $this->actingAs($user)->get(action([ProfileController::class, 'index']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('profiles.0.id', $first->getRouteKey())
        ->where('profiles.0.is_current', false)
        ->where('profiles.1.id', $second->getRouteKey())
        ->where('profiles.1.is_current', true)
    );
});

it('forbids switching another user profile', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $owner->getKey(),
    ]);

    $response = $this->actingAs($intruder)->post(action(SwitchProfileController::class, $profile));

    $response->assertForbidden();
    $response->assertSessionMissing('profiles.current');
});

it('creates a profile for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(action([ProfileController::class, 'store']), [
        'name' => 'Kids',
        'is_kids' => true,
    ]);

    $response->assertRedirect();

    expect($user->profiles()->where('name', 'Kids')->exists())->toBeTrue();
});

it('updates an owned profile', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Main',
    ]);

    $response = $this->actingAs($user)->put(action([ProfileController::class, 'update'], $profile), [
        'name' => 'Updated',
        'is_kids' => false,
        'is_primary' => true,
    ]);

    $response->assertRedirect();

    expect($profile->fresh()?->name)->toBe('Updated')
        ->and($profile->fresh()?->is_primary)->toBeTrue();
});

it('deletes an owned profile', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    $response = $this->actingAs($user)->delete(action([ProfileController::class, 'destroy'], $profile));

    $response->assertRedirect();
    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeFalse();
});

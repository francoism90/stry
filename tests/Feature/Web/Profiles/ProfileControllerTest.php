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
        ->has('items.data', 2)
    );
});

it('sorts profiles by created_at when requested', function () {
    $user = User::factory()->create();

    $older = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => false,
        'created_at' => now()->subDay(),
    ]);

    $newer = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'is_primary' => false,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(action([ProfileController::class, 'index'], [
        'sort' => 'created_at',
    ]));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('sort', 'created_at')
        ->where('items.data.0.id', $newer->getRouteKey())
        ->where('items.data.1.id', $older->getRouteKey())
    );
});

it('rejects unknown profile sort values', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([ProfileController::class, 'index'], [
        'sort' => 'invalid',
    ]));

    $response->assertSessionHasErrors('sort');
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
        ->where('items.data.0.id', $first->getRouteKey())
        ->where('items.data.1.id', $second->getRouteKey())
        ->where('profile.id', $second->getRouteKey())
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

it('marks the first created profile as primary', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(action([ProfileController::class, 'store']), [
        'name' => 'Main',
        'is_kids' => false,
    ])->assertRedirect();

    $profile = $user->profiles()->first();

    expect($profile)->not->toBeNull()
        ->and($profile?->is_primary)->toBeTrue();
});

it('unmarks existing primary profile when creating a new primary profile', function () {
    $user = User::factory()->create();

    $primary = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Primary',
        'is_primary' => true,
    ]);

    $this->actingAs($user)->post(action([ProfileController::class, 'store']), [
        'name' => 'New Primary',
        'is_kids' => false,
        'is_primary' => true,
    ])->assertRedirect();

    expect($primary->fresh()?->is_primary)->toBeFalse();
    expect($user->profiles()->where('name', 'New Primary')->first()?->is_primary)->toBeTrue();
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

it('unmarks other profiles when updating a profile as primary', function () {
    $user = User::factory()->create();

    $primary = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Primary',
        'is_primary' => true,
    ]);

    $secondary = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Secondary',
        'is_primary' => false,
    ]);

    $this->actingAs($user)->put(action([ProfileController::class, 'update'], $secondary), [
        'name' => 'Secondary',
        'is_kids' => false,
        'is_primary' => true,
    ])->assertRedirect();

    expect($secondary->fresh()?->is_primary)->toBeTrue()
        ->and($primary->fresh()?->is_primary)->toBeFalse();
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

it('forgets current profile session key when deleting the selected profile', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    $response = $this->actingAs($user)
        ->withSession(['profiles.current' => $profile->getRouteKey()])
        ->delete(action([ProfileController::class, 'destroy'], $profile));

    $response->assertRedirect();
    $response->assertSessionMissing('profiles.current');
});

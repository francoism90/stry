<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupController;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Users\Models\User;

// index

it('allows admins to view the group index', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertSuccessful();
});

it('redirects guests from viewing the group index', function () {
    $response = $this->get(action([GroupController::class, 'index']));

    $response->assertRedirect();
});

it('allows regular users to view the group index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertSuccessful();
});

it('only returns groups that belong to the authenticated user on index', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $owned = Group::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Owned Group Alpha',
    ]);

    Group::factory()->create([
        'user_id' => $other->getKey(),
        'name' => 'Foreign Group Beta',
    ]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertSuccessful();
    $response->assertSee($owned->name);
    $response->assertDontSee('Foreign Group Beta');
});

it('excludes mixer groups from the group index', function () {
    $user = User::factory()->create();

    $custom = Group::factory()->create([
        'user_id' => $user->getKey(),
        'type' => GroupType::Custom,
        'name' => 'Visible Custom Group',
    ]);

    Group::factory()->create([
        'user_id' => $user->getKey(),
        'type' => GroupType::Mixer,
        'name' => 'Hidden Mixer Group',
    ]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertSuccessful();
    $response->assertSee($custom->name);
    $response->assertDontSee('Hidden Mixer Group');
});

it('accepts the mixer scope filter on the group index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index'], ['filter' => ['scope' => 'mixer']]));

    $response->assertSuccessful();
});

// show

it('allows the owner to view their group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'show'], $group));

    $response->assertSuccessful();
});

it('forbids other users from viewing a group they do not own', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $owner->getKey()]);

    $response = $this->actingAs($other)->get(action([GroupController::class, 'show'], $group));

    $response->assertForbidden();
});

// store

it('allows admins to create a group', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->post(action([GroupController::class, 'store']), [
        'name' => 'My New Group',
        'type' => GroupType::Custom->value,
    ]);

    $response->assertRedirect();
    expect(Group::query()->where('name', 'My New Group')->exists())->toBeTrue();
});

it('allows regular users to create a group', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(action([GroupController::class, 'store']), [
        'name' => 'My Group',
        'type' => GroupType::Custom->value,
    ]);

    $response->assertRedirect();
    expect(Group::query()->where('name', 'My Group')->exists())->toBeTrue();
});

it('validates required fields on store', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->post(action([GroupController::class, 'store']), []);

    $response->assertInvalid(['name']);
});

// update

it('allows the owner to update their group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->custom()->create(['user_id' => $user->getKey(), 'name' => 'Old Name']);

    $response = $this->actingAs($user)->put(action([GroupController::class, 'update'], $group), [
        'name' => 'New Name',
    ]);

    $response->assertRedirect();
    expect($group->fresh()->name)->toBe('New Name');
});

it('forbids other users from updating a group they do not own', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Original Name']);

    $response = $this->actingAs($other)->put(action([GroupController::class, 'update'], $group), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
    expect($group->fresh()->name)->toBe('Original Name');
});

it('validates required fields on update', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->put(action([GroupController::class, 'update'], $group), []);

    $response->assertInvalid(['name']);
});

// destroy

it('allows the owner to delete their group', function () {
    $user = User::factory()->create();
    $group = Group::factory()->custom()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->delete(action([GroupController::class, 'destroy'], $group));

    $response->assertRedirect();
    expect(Group::query()->find($group->getKey()))->toBeNull();
});

it('forbids other users from deleting a group they do not own', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $owner->getKey()]);

    $response = $this->actingAs($other)->delete(action([GroupController::class, 'destroy'], $group));

    $response->assertForbidden();
    expect(Group::query()->find($group->getKey()))->not->toBeNull();
});

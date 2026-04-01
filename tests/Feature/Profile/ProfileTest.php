<?php

declare(strict_types=1);

use Domain\Profiles\Models\Profile;
use Domain\Profiles\Policies\ProfilePolicy;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('can create a profile with required attributes', function () {
    $user = User::factory()->create();

    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Main',
    ]);

    expect($profile->exists)->toBeTrue()
        ->and($profile->user_id)->toBe($user->getKey())
        ->and($profile->name)->toBe('Main')
        ->and($profile->is_kids)->toBeFalse()
        ->and($profile->is_primary)->toBeFalse();
});

it('belongs to a user', function () {
    $user = User::factory()->create();

    $profile = Profile::factory()->create([
        'user_id' => $user->getKey(),
    ]);

    expect($profile->user)->toBeInstanceOf(User::class)
        ->and($profile->user->getKey())->toBe($user->getKey());
});

it('allows a user to have multiple profiles', function () {
    $user = User::factory()->create();

    Profile::factory()->count(3)->create([
        'user_id' => $user->getKey(),
    ]);

    expect($user->profiles()->count())->toBe(3);
});

it('uses ULIDs as route identifiers', function () {
    $profile = Profile::factory()->create();

    expect($profile->ulid)->not->toBeNull()
        ->and($profile->getRouteKeyName())->toBe('ulid')
        ->and($profile->getRouteKey())->toBe($profile->ulid);
});

it('can resolve a profile from a ulid', function () {
    $profile = Profile::factory()->create();

    $resolved = Profile::findFromUlid($profile->ulid);

    expect($resolved)->toBeInstanceOf(Profile::class)
        ->and($resolved?->getKey())->toBe($profile->getKey());
});

it('resolves the profile policy through gate', function () {
    expect(Gate::getPolicyFor(Profile::class))->toBeInstanceOf(ProfilePolicy::class);
});

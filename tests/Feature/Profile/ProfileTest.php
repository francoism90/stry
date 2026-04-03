<?php

declare(strict_types=1);

use Domain\Profiles\Models\Profile;
use Domain\Profiles\Policies\ProfilePolicy;
use Domain\Profiles\States\Disabled;
use Domain\Profiles\States\Enabled;
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
        ->and($profile->is_primary)->toBeFalse()
        ->and($profile->state->equals(Enabled::class))->toBeTrue()
        ->and($profile->settings->toArray())->toBe([]);
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

it('casts profile settings to an array object', function () {
    $profile = Profile::factory()->create([
        'settings' => [
            'language' => 'en',
            'autoplay_next' => true,
        ],
    ]);

    expect($profile->settings)->toBeInstanceOf(ArrayObject::class)
        ->and($profile->settings->toArray())->toBe([
            'language' => 'en',
            'autoplay_next' => true,
        ]);
});

it('can scope enabled and disabled profiles', function () {
    $user = User::factory()->create();

    $enabledProfile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'state' => Enabled::class,
    ]);

    $disabledProfile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'state' => Disabled::class,
    ]);

    expect($user->profiles()->enabled()->pluck('id')->all())->toBe([$enabledProfile->getKey()])
        ->and($user->profiles()->disabled()->pluck('id')->all())->toBe([$disabledProfile->getKey()]);
});

it('returns the primary profile when one exists', function () {
    $user = User::factory()->create();

    $firstProfile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Alpha',
        'is_primary' => false,
    ]);

    $primaryProfile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Bravo',
        'is_primary' => true,
    ]);

    expect($user->currentProfile()?->is($primaryProfile))->toBeTrue();
});

it('falls back to the first profile when no primary exists', function () {
    $user = User::factory()->create();

    $firstProfile = Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Alpha',
        'is_primary' => false,
    ]);

    Profile::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Bravo',
        'is_primary' => false,
    ]);

    expect($user->currentProfile()?->is($firstProfile))->toBeTrue();
});

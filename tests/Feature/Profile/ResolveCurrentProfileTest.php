<?php

declare(strict_types=1);

use App\Web\Profiles\Middlewares\ResolveCurrentProfile;
use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

it('sets a null currentProfile request attribute for unauthenticated requests', function () {
    $request = Request::create('/');

    $middleware = new ResolveCurrentProfile;
    $middleware->handle($request, fn ($req) => new Response);

    expect($request->attributes->get('currentProfile'))->toBeNull()
        ->and(Profile::current())->toBeNull();
});

it('sets the session profile on the request when profiles.current is set', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->for($user)->create(['is_kids' => false]);

    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);
    $request->setLaravelSession(session()->driver());
    session()->put('profiles.current', $profile->ulid);

    $middleware = new ResolveCurrentProfile;
    $middleware->handle($request, fn ($req) => new Response);

    $resolved = $request->attributes->get('currentProfile');

    expect($resolved)->toBeInstanceOf(Profile::class)
        ->and($resolved->getKey())->toBe($profile->getKey())
        ->and($resolved->is_kids)->toBeFalse()
        ->and(Profile::current()?->getKey())->toBe($profile->getKey());
});

it('sets a kids profile on the request when the session profile is kids', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->for($user)->create(['is_kids' => true]);

    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);
    $request->setLaravelSession(session()->driver());
    session()->put('profiles.current', $profile->ulid);

    $middleware = new ResolveCurrentProfile;
    $middleware->handle($request, fn ($req) => new Response);

    expect($request->attributes->get('currentProfile'))->toBeInstanceOf(Profile::class)
        ->and($request->attributes->get('currentProfile')->is_kids)->toBeTrue()
        ->and(Profile::current()?->isKids())->toBeTrue();
});

it('falls back to the primary profile when no session is set', function () {
    $user = User::factory()->create();
    $primary = Profile::factory()->for($user)->create(['is_primary' => true]);
    Profile::factory()->for($user)->create(['is_primary' => false]);

    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new ResolveCurrentProfile;
    $middleware->handle($request, fn ($req) => new Response);

    $resolved = $request->attributes->get('currentProfile');

    expect($resolved)->toBeInstanceOf(Profile::class)
        ->and($resolved->getKey())->toBe($primary->getKey())
        ->and(Profile::current()?->getKey())->toBe($primary->getKey());
});

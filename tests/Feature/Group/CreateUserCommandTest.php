<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

it('creates a user without a role by default', function () {
    artisan('users:create')
        ->expectsQuestion('Name', 'Jane Doe')
        ->expectsQuestion('Email', 'jane@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertSuccessful();

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->name)->toBe('Jane Doe')
        ->and($user->hasAnyRole('admin', 'super-admin'))->toBeFalse();
});

it('assigns the admin role when --admin flag is given', function () {
    artisan('users:create --admin')
        ->expectsQuestion('Name', 'Admin User')
        ->expectsQuestion('Email', 'admin@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('super-admin'))->toBeFalse();
});

it('assigns the super-admin role when --super-admin flag is given', function () {
    artisan('users:create --super-admin')
        ->expectsQuestion('Name', 'Super Admin')
        ->expectsQuestion('Email', 'superadmin@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertSuccessful();

    $user = User::query()->where('email', 'superadmin@example.com')->firstOrFail();

    expect($user->hasRole('super-admin'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});

it('assigns super-admin role and not admin when both flags are given', function () {
    artisan('users:create --admin --super-admin')
        ->expectsQuestion('Name', 'Both Flags')
        ->expectsQuestion('Email', 'both@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertSuccessful();

    $user = User::query()->where('email', 'both@example.com')->firstOrFail();

    expect($user->hasRole('super-admin'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});

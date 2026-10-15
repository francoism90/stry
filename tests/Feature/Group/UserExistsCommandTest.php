<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

it('fails when there are no users', function () {
    artisan('users:exists')->assertFailed();
});

it('succeeds when a user exists', function () {
    User::factory()->create();

    artisan('users:exists')->assertSuccessful();
});

it('only counts users with the given role', function () {
    $user = User::factory()->create();

    artisan('users:exists --super-admin')->assertFailed();

    $user->assignRole('super-admin');

    artisan('users:exists --super-admin')->assertSuccessful();
});

<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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

it('creates a user without prompts, reading the password from stdin', function () {
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, "correct-horse-battery\n");
    rewind($stream);

    $input = new ArrayInput([
        'command' => 'users:create',
        '--name' => 'Ada Lovelace',
        '--email' => 'ada@example.com',
        '--password-stdin' => true,
        '--super-admin' => true,
    ]);
    $input->setStream($stream);
    $input->setInteractive(false);

    expect(app(Kernel::class)->handle($input, new BufferedOutput))->toBe(0);

    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

    expect($user->name)->toBe('Ada Lovelace')
        ->and(Hash::check('correct-horse-battery', $user->password))->toBeTrue()
        ->and($user->hasRole('super-admin'))->toBeTrue();
});

it('fails without prompts when a value is missing', function () {
    artisan('users:create', ['--email' => 'ada@example.com', '--no-interaction' => true])
        ->assertFailed();

    expect(User::query()->where('email', 'ada@example.com')->exists())->toBeFalse();
});

it('fails with the validation errors instead of throwing', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    artisan('users:create')
        ->expectsQuestion('Name', 'Jane Doe')
        ->expectsQuestion('Email', 'taken@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertFailed();
});

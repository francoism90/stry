<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

use function Pest\Laravel\artisan;

beforeEach(fn () => Process::fake());

it('restarts the queue and octane workers', function () {
    artisan('app:restart')->assertSuccessful();

    expect(Cache::get('illuminate:queue:restart'))->not->toBeNull();

    Process::assertRan(fn ($process) => str_contains($process->command, 'artisan octane:reload'));
});

it('migrates and restarts the workers on update', function () {
    artisan('app:update')->assertSuccessful();

    expect(Cache::get('illuminate:queue:restart'))->not->toBeNull();

    Process::assertRan(fn ($process) => str_contains($process->command, 'artisan octane:reload'));
});

it('does not restart the workers on update with --no-restart', function () {
    artisan('app:update --no-restart')->assertSuccessful();

    expect(Cache::get('illuminate:queue:restart'))->toBeNull();

    Process::assertNothingRan();
});

it('does not ask for an admin on install when a super-admin exists', function () {
    User::factory()->create()->assignRole('super-admin');

    artisan('app:install')
        ->expectsConfirmation('Create the S3 buckets and apply their CORS policy?', 'no')
        ->doesntExpectOutputToContain('Create a super-admin account?')
        ->assertSuccessful();
});

it('offers to create a super-admin on install when there is none', function () {
    artisan('app:install')
        ->expectsConfirmation('Create the S3 buckets and apply their CORS policy?', 'no')
        ->expectsConfirmation('Create a super-admin account?', 'yes')
        ->expectsQuestion('Name', 'Jane Doe')
        ->expectsQuestion('Email', 'jane@example.com')
        ->expectsQuestion('Password', 'password')
        ->assertSuccessful();

    expect(User::query()->where('email', 'jane@example.com')->firstOrFail()->hasRole('super-admin'))->toBeTrue();
});

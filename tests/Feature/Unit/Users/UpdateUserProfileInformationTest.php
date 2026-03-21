<?php

declare(strict_types=1);

use Domain\Users\Actions\UpdateUserProfileInformation;
use Domain\Users\Models\User;
use Illuminate\Validation\ValidationException;

it('updates the user name and email', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $action = new UpdateUserProfileInformation;

    $action->update($user, [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

it('allows resubmitting the same email without failing unique validation', function (): void {
    $user = User::factory()->create([
        'name' => 'Same User',
        'email' => 'same@example.com',
    ]);

    $action = new UpdateUserProfileInformation;

    // Should not throw a ValidationException for duplicate email
    $action->update($user, [
        'name' => 'Same User Updated',
        'email' => 'same@example.com',
    ]);

    $user->refresh();

    expect($user->name)->toBe('Same User Updated');
    expect($user->email)->toBe('same@example.com');
});

it('fails validation when email belongs to another user', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $user = User::factory()->create(['email' => 'mine@example.com']);

    $action = new UpdateUserProfileInformation;

    expect(fn () => $action->update($user, [
        'name' => 'My Name',
        'email' => 'taken@example.com',
    ]))->toThrow(ValidationException::class);
});

it('fails validation when name is missing', function (): void {
    $user = User::factory()->create();

    $action = new UpdateUserProfileInformation;

    expect(fn () => $action->update($user, [
        'email' => 'test@example.com',
    ]))->toThrow(ValidationException::class);
});

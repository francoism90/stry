<?php

declare(strict_types=1);

use App\Api\Videos\Broadcasting\VideoLibraryChannel;
use Domain\Users\Models\User;

it('allows super-admins to join the shared videos channel', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    expect((new VideoLibraryChannel)->join($user))->toBeTrue();
});

it('forbids regular users from joining the shared videos channel', function () {
    $user = User::factory()->create();

    expect((new VideoLibraryChannel)->join($user))->toBeFalse();
});

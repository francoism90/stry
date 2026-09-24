<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Modules\Api\Videos\Broadcasting\VideoLibraryChannel;

it('allows super-admins to join the shared videos channel', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    expect((new VideoLibraryChannel)->join($user))->toBeTrue();
});

it('forbids regular users from joining the shared videos channel', function () {
    $user = User::factory()->create();

    expect((new VideoLibraryChannel)->join($user))->toBeFalse();
});

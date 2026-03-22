<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a custom group', function () {
    $user = User::factory()->create();
    $group = $user->findOrCreateGroup(GroupType::Custom, 'foo');

    expect($group->exists)->toBeTrue()
        ->and($group->user_id)->toBe($user->id)
        ->and($group->name)->toBe('foo')
        ->and($group->type)->toBe(GroupType::Custom);
});

it('can create like group', function () {
    $user = User::factory()->create();
    $group = $user->groupFor(GroupType::Liked);

    expect($group->exists)->toBeTrue()
        ->and($group->user_id)->toBe($user->id)
        ->and($group->type)->toBe(GroupType::Liked);
});

it('can attach and detach videos to saved group', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $user->markInGroup($video, GroupType::Saved);

    $group = $user->groupFor(GroupType::Saved);
    $group->refresh();

    expect($user->isInGroup($video, GroupType::Saved))->toBeTrue();

    $user->toggleInGroup($video, GroupType::Saved);

    $group->refresh();

    expect($user->isInGroup($video, GroupType::Saved))->toBeFalse();
});

it('can attach and detach videos to like group', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $user->markInGroup($video, GroupType::Liked);

    $group = $user->groupFor(GroupType::Liked);
    $group->refresh();

    expect($user->isInGroup($video, GroupType::Liked))->toBeTrue();

    $user->toggleInGroup($video, GroupType::Liked);
    $group->refresh();

    expect($user->isInGroup($video, GroupType::Liked))->toBeFalse();
});

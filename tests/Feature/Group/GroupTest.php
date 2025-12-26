<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Groups\Enums\GroupType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a group with required attributes', function () {
    $user = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $user->id,
        'name' => 'Favorites',
        'type' => GroupType::Favorited,
    ]);

    expect($group->exists)->toBeTrue()
        ->and($group->user_id)->toBe($user->id)
        ->and($group->type)->toBe(GroupType::Favorited)
        ->and($group->name)->toBe('Favorites');
});

it('can attach and detach videos to group', function () {
    $user = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $user->id,
        'type' => GroupType::Saved,
        'name' => 'Saved',
    ]);

    $video = Video::factory()->create();

    $group->attach($video);
    $group->refresh();

    expect($group->groupables)->toContain($video);

    $group->detach($video);
    $group->refresh();

    expect($group->groupables)->not->toContain($video);
});

it('can toggle video in group', function () {
    $user = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $user->id,
        'type' => GroupType::Viewed,
        'name' => 'Viewed',
    ]);

    $video = Video::factory()->create();

    $group->toggle($video);
    $group->refresh();

    expect($group->groupables)->toContain($video);

    $group->toggle($video);
    $group->refresh();

    expect($group->groupables)->not->toContain($video);
});

it('can check if video is in group', function () {
    $user = User::factory()->create();

    $group = Group::factory()->create([
        'user_id' => $user->id,
        'type' => GroupType::Favorited,
        'name' => 'Favorites',
    ]);

    $video = Video::factory()->create();

    $group->attach($video);
    $group->refresh();

    expect($group->has($video))->toBeTrue();

    $group->detach($video);
    $group->refresh();

    expect($group->has($video))->toBeFalse();
});

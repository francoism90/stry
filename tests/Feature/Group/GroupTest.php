<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Groups\Enums\GroupType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create favorite group', function () {
    $user = User::factory()->create();

    // Use HasGroups logic to get or create a unique group
    $group = $user->favoritedGroup();

    expect($group->exists)->toBeTrue()
        ->and($group->user_id)->toBe($user->id)
        ->and($group->type)->toBe(GroupType::Favorited);
});

it('can attach and detach videos to saved group', function () {
    $user = User::factory()->create();

    $video = Video::factory()->create();

    // Attach using HasGroups method
    $user->markAsSaved($video);

    $group = $user->savedGroup();
    $group->refresh();

    expect($group->groupables)->toContain($video);

    // Detach using HasGroups toggle method
    $user->toggleSaved($video);

    $group->refresh();

    expect($group->groupables)->not->toContain($video);
});

it('can toggle video in viewed group', function () {
    $user = User::factory()->create();

    $group = $user->viewedGroup();

    $video = Video::factory()->create();

    $group->toggleViewed($video);
    $group->refresh();

    expect($group->groupables)->toContain($video);

    $group->toggleViewed($video);
    $group->refresh();

    expect($group->groupables)->not->toContain($video);
});

it('can toggle video in favorited group', function () {
    $user = User::factory()->create();

    $group = $user->favoritedGroup();

    $video = Video::factory()->create();

    $group->toggleFavorited($video);
    $group->refresh();

    expect($group->has($video))->toBeTrue();

    $group->toggleFavorited($video);
    $group->refresh();

    expect($group->has($video))->toBeFalse();
});

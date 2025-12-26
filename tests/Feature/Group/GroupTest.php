<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a custom group', function () {
    $user = User::factory()->create();
    $group = $user->customGroup('foo');

    expect($group->exists)->toBeTrue()
        ->and($group->user_id)->toBe($user->id)
        ->and($group->name)->toBe('foo')
        ->and($group->type)->toBe(GroupType::Custom);
});

it('can create favorite group', function () {
    $user = User::factory()->create();
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

    expect($user->isSaved($video))->toBeTrue();

    // Detach using HasGroups toggle method
    $user->toggleSaved($video);

    $group->refresh();

    expect($user->isSaved($video))->toBeFalse();
});

it('can attach and detach videos to favorited group', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    // Attach using HasGroups method
    $user->markAsFavorited($video);

    $group = $user->favoritedGroup();
    $group->refresh();

    expect($user->isFavorite($video))->toBeTrue();

    // Detach using HasGroups toggle method
    $user->toggleFavorited($video);
    $group->refresh();

    expect($user->isFavorite($video))->toBeFalse();
});

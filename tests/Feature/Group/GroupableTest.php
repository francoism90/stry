<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('indexes the watched share stored with the watch position', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $user->markInGroup($video, GroupType::Viewed, ['time' => 50, 'progress' => 0.4]);

    $groupable = $user->groupFor(GroupType::Viewed)->getGroupable($video);

    expect($groupable->toSearchableArray()['progress'])->toBe(0.4);
});

it('works out the watched share for positions stored without one', function () {
    $user = User::factory()->create();
    $video = Video::factory()->withDuration(200)->create();

    $user->markInGroup($video, GroupType::Viewed, ['time' => 50]);

    $groupable = $user->groupFor(GroupType::Viewed)->getGroupable($video);

    expect($groupable->toSearchableArray()['progress'])->toBe(0.25);
});

it('indexes no watched share for memberships without a watch position', function () {
    $user = User::factory()->create();
    $video = Video::factory()->withDuration(200)->create();

    $user->markInGroup($video, GroupType::Saved);

    $groupable = $user->groupFor(GroupType::Saved)->getGroupable($video);

    expect($groupable->toSearchableArray()['progress'])->toBeNull();
});

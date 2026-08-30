<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupClearController;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

beforeEach(function () {
    $this->withoutDefer();
});

it('allows the owner to clear their own viewed history', function () {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->viewed()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $response = $this->actingAs($user)->post(action(GroupClearController::class, $group));

    $response->assertRedirect();
    expect($group->fresh()->videos()->count())->toBe(0);
});

it('allows the owner to clear their own custom collection', function () {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->custom()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $response = $this->actingAs($user)->post(action(GroupClearController::class, $group));

    $response->assertRedirect();
    expect($group->fresh()->videos()->count())->toBe(0);
});

it('forbids other users from clearing a viewed history they do not own', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = Group::factory()->for($owner)->viewed()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $response = $this->actingAs($other)->post(action(GroupClearController::class, $group));

    $response->assertForbidden();
    expect($group->fresh()->videos()->count())->toBe(1);
});

it('forbids the owner from clearing their liked or saved groups', function (GroupType $type) {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->create(['type' => $type]);
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $response = $this->actingAs($user)->post(action(GroupClearController::class, $group));

    $response->assertForbidden();
    expect($group->fresh()->videos()->count())->toBe(1);
})->with([GroupType::Liked, GroupType::Saved]);

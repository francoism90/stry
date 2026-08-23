<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupController;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('lists the pinned groups before custom groups, in liked, saved, viewed order', function () {
    $user = User::factory()->create();
    $custom = Group::factory()->for($user)->create(['type' => GroupType::Custom, 'name' => 'Custom Mix']);
    $viewed = Group::factory()->for($user)->create(['type' => GroupType::Viewed]);
    $liked = Group::factory()->for($user)->create(['type' => GroupType::Liked]);
    $saved = Group::factory()->for($user)->create(['type' => GroupType::Saved]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('collections', 4)
        ->where('collections.0.id', $liked->getRouteKey())
        ->where('collections.1.id', $saved->getRouteKey())
        ->where('collections.2.id', $viewed->getRouteKey())
        ->where('collections.3.id', $custom->getRouteKey())
    );
});

it('excludes mixer groups from the collections prop', function () {
    $user = User::factory()->create();
    Group::factory()->for($user)->create(['type' => GroupType::Mixer]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->has('collections', 0));
});

it('does not include collections belonging to other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Group::factory()->for($other)->create(['type' => GroupType::Custom]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->has('collections', 0));
});

it('limits the collections prop to the twenty most recent custom groups by name', function () {
    $user = User::factory()->create();
    Group::factory()->for($user)->count(25)->create(['type' => GroupType::Custom]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->has('collections', 20));
});

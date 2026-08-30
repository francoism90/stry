<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupController;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shares the viewed history group for the authenticated user', function () {
    $user = User::factory()->create();
    $viewed = Group::factory()->for($user)->create(['type' => GroupType::Viewed]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('history.id', $viewed->getRouteKey())
        ->where('history.type', GroupType::Viewed->value)
    );
});

it('shares null when the user has no viewed history yet', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->where('history', null));
});

it('does not share viewed history belonging to other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Group::factory()->for($other)->create(['type' => GroupType::Viewed]);

    $response = $this->actingAs($user)->get(action([GroupController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->where('history', null));
});

it('shares null for guests', function () {
    $response = $this->get('/login');

    $response->assertInertia(fn (Assert $page) => $page->where('history', null));
});

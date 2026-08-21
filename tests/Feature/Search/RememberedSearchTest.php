<?php

declare(strict_types=1);

use App\Web\Groups\Controllers\GroupController;
use App\Web\Tags\Controllers\TagController;
use App\Web\Videos\Controllers\VideoController;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('remembers the last search term for the global search bar', function (string $controller) {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action([$controller, 'index'], ['query' => 'remembered term']));

    $response = $this->actingAs($user)->get(action([$controller, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->where('search', 'remembered term'));
})->with([
    VideoController::class,
    TagController::class,
    GroupController::class,
]);

it('does not overwrite the remembered search term with an empty query', function (string $controller) {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action([$controller, 'index'], ['query' => 'remembered term']));
    $this->actingAs($user)->get(action([$controller, 'index']));

    $response = $this->actingAs($user)->get(action([$controller, 'index']));

    $response->assertInertia(fn (Assert $page) => $page->where('search', 'remembered term'));
})->with([
    VideoController::class,
    TagController::class,
    GroupController::class,
]);

it('keeps remembered search terms isolated per resource', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action([TagController::class, 'index'], ['query' => 'tag term']));
    $this->actingAs($user)->get(action([VideoController::class, 'index'], ['query' => 'video term']));

    $tags = $this->actingAs($user)->get(action([TagController::class, 'index']));
    $tags->assertInertia(fn (Assert $page) => $page->where('search', 'tag term'));

    $videos = $this->actingAs($user)->get(action([VideoController::class, 'index']));
    $videos->assertInertia(fn (Assert $page) => $page->where('search', 'video term'));
});

it('shares the remembered search term between the videos index and a tag view', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($user)->get(action([VideoController::class, 'index'], ['query' => 'shared term']));

    $response = $this->actingAs($user)->get(action([TagController::class, 'show'], $tag));

    $response->assertInertia(fn (Assert $page) => $page->where('search', 'shared term'));
});

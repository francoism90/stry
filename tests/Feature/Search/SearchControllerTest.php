<?php

declare(strict_types=1);

use App\Web\Search\Controllers\SearchController;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the search index page for the given query', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SearchController::class, ['query' => 'some term']));

    $response->assertOk()->assertInertia(
        fn (Assert $page) => $page
            ->component('Search/SearchIndex')
            ->where('search', 'some term')
    );
});

it('remembers the last non-empty search term for the global search bar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action(SearchController::class, ['query' => 'remembered term']));

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->where('search', 'remembered term'));
});

it('does not overwrite the remembered search term with an empty query', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action(SearchController::class, ['query' => 'remembered term']));
    $this->actingAs($user)->get(action(SearchController::class));

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page->where('search', 'remembered term'));
});

it('redirects unauthenticated guests', function () {
    $response = $this->get(action(SearchController::class, ['query' => 'test']));

    $response->assertRedirect();
});

it('links "see all" results to the resource index pages filtered by query', function (string $routeName) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route($routeName, ['query' => 'some term']));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page->where('query', 'some term'));
})->with([
    'videos.index',
    'tags.index',
    'collections.index',
]);

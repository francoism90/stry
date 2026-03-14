<?php

declare(strict_types=1);

use App\Web\Search\Controllers\SearchController;
use App\Web\Search\Controllers\SearchGroupsController;
use App\Web\Search\Controllers\SearchTagsController;
use App\Web\Search\Controllers\SearchVideosController;
use Domain\Users\Models\User;

// SearchController (overview)

it('allows admins to view the search overview', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action(SearchController::class, ['query' => 'avatar']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('App/Search/SearchIndex')
        ->has('search')
        ->has('videos')
        ->has('tags')
        ->has('collections')
    );
});

it('allows regular users to view the search overview', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SearchController::class, ['query' => 'avatar']));

    $response->assertSuccessful();
});

it('redirects guests from the search overview', function () {
    $response = $this->get(action(SearchController::class));

    $response->assertRedirect();
});

// SearchVideosController

it('allows admins to view video search results', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action(SearchVideosController::class, ['query' => 'avatar']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('App/Search/SearchVideos')
        ->has('search')
        ->has('order')
        ->has('orders')
        ->has('items')
    );
});

it('allows regular users to view video search results', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SearchVideosController::class, ['query' => 'avatar']));

    $response->assertSuccessful();
});

it('redirects guests from video search results', function () {
    $response = $this->get(action(SearchVideosController::class, ['query' => 'avatar']));

    $response->assertRedirect();
});

// SearchTagsController

it('allows admins to view tag search results', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action(SearchTagsController::class, ['query' => 'action']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('App/Search/SearchTags')
        ->has('search')
        ->has('type')
        ->has('types')
        ->has('items')
    );
});

it('allows regular users to view tag search results', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SearchTagsController::class, ['query' => 'action']));

    $response->assertSuccessful();
});

it('redirects guests from tag search results', function () {
    $response = $this->get(action(SearchTagsController::class, ['query' => 'action']));

    $response->assertRedirect();
});

// SearchGroupsController

it('allows admins to view collection search results', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action(SearchGroupsController::class, ['query' => 'marvel']));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('App/Search/SearchCollections')
        ->has('search')
        ->has('order')
        ->has('orders')
        ->has('items')
    );
});

it('allows regular users to view collection search results', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action(SearchGroupsController::class, ['query' => 'marvel']));

    $response->assertSuccessful();
});

it('redirects guests from collection search results', function () {
    $response = $this->get(action(SearchGroupsController::class, ['query' => 'marvel']));

    $response->assertRedirect();
});

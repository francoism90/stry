<?php

declare(strict_types=1);

use App\Web\Shuffle\Controllers\ShuffleController;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

it('redirects guests to login', function () {
    $response = $this->get(action(ShuffleController::class, 'videos'));

    $response->assertRedirect();
    $response->assertRedirectToRoute('login');
});

it('redirects to a random verified video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->get(action(ShuffleController::class, 'videos'));

    $response->assertRedirect(route('videos.show', $video));
    $response->assertSessionHas('shuffle.videos', [$video->getKey()]);
});

it('redirects to a random tag that has videos', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    $tag = Tag::factory()->create();
    $tag->videos()->attach($video);

    Tag::factory()->create(); // A tag without any videos should never be picked.

    $response = $this->actingAs($user)->get(action(ShuffleController::class, 'tags'));

    $response->assertRedirect(route('tags.show', $tag));
    $response->assertSessionHas('shuffle.tags', [$tag->getKey()]);
});

it('excludes videos already shown in this session', function () {
    $user = User::factory()->create();
    $shown = Video::factory()->create();
    $next = Video::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['shuffle.videos' => [$shown->getKey()]])
        ->get(action(ShuffleController::class, 'videos'));

    $response->assertRedirect(route('videos.show', $next));
    $response->assertSessionHas('shuffle.videos', [$shown->getKey(), $next->getKey()]);
});

it('resets the history and redirects home once every video has been shown', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['shuffle.videos' => [$video->getKey()]])
        ->get(action(ShuffleController::class, 'videos'));

    $response->assertRedirectToRoute('home');
    $response->assertSessionMissing('shuffle.videos');
});

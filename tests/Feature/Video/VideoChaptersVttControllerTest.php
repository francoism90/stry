<?php

declare(strict_types=1);

use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Pending;

it('serves the current chapters as webvtt', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    Chapter::factory()->intro()->create(['video_id' => $video->getKey(), 'label' => 'Opening']);

    $this->actingAs($user)->get(route('videos.chapters-vtt', $video))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/vtt; charset=utf-8')
        ->assertSee('WEBVTT')
        ->assertSee('Opening');
});

it('has no chapter track for videos without chapters', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $this->actingAs($user)->get(route('videos.chapters-vtt', $video))->assertNotFound();
});

it('refuses videos the user may not view', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['state' => Pending::class]);
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

    $this->actingAs($user)->get(route('videos.chapters-vtt', $video))->assertForbidden();
});

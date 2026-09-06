<?php

declare(strict_types=1);

use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Pending;
use Domain\Videos\States\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the chapters webvtt for an authorized viewer', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['state' => Verified::class]);
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

    $response = $this->actingAs($user)->get(route('api.play.chapters', $video));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/vtt; charset=utf-8');

    expect($response->getContent())->toStartWith('WEBVTT');
});

it('denies access to a video the viewer cannot view', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['state' => Pending::class]);

    $response = $this->actingAs($user)->get(route('api.play.chapters', $video));

    $response->assertForbidden();
});

it('requires authentication', function () {
    $video = Video::factory()->create(['state' => Verified::class]);

    $response = $this->get(route('api.play.chapters', $video));

    $response->assertUnauthorized();
});

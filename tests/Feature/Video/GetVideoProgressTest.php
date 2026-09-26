<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('falls back to the progress stored in the viewed group when nothing is cached', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['duration' => 100]);
    $user->markInGroup($video, GroupType::Viewed, ['time' => 42.5]);

    $progress = app(GetVideoProgress::class)->handle($video, $user);

    expect($progress)->toBe(42.5);
});

it('returns no progress when the video was never viewed', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['duration' => 100]);

    $progress = app(GetVideoProgress::class)->handle($video, $user);

    expect($progress)->toBe(0.0);
});

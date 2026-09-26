<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createVideoWithDuration(int $seconds): Video
{
    $video = Video::factory()->create();

    $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [
            'streams' => [
                ['codec_type' => 'video', 'duration' => $seconds],
            ],
        ],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    return $video->refresh();
}

it('falls back to the progress stored in the viewed group when nothing is cached', function () {
    $user = User::factory()->create();
    $video = createVideoWithDuration(100);
    $user->markInGroup($video, GroupType::Viewed, ['time' => 42.5]);

    $progress = app(GetVideoProgress::class)->handle($video, $user);

    expect($progress)->toBe(42.5);
});

it('returns no progress when the video was never viewed', function () {
    $user = User::factory()->create();
    $video = createVideoWithDuration(100);

    $progress = app(GetVideoProgress::class)->handle($video, $user);

    expect($progress)->toBe(0.0);
});

<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Listeners\SyncVideoProgress;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function watchVideo(Video $video, User $user, float $time): void
{
    app(SyncVideoProgress::class)->handle(new VideoHasBeenViewedEvent($video, $user, ['time' => $time]));
}

function storedWatchTime(Video $video, User $user): ?float
{
    $time = $user->groupFor(GroupType::Viewed)->getGroupable($video)?->options['time'];

    return $time === null ? null : (float) $time;
}

beforeEach(function () {
    PlaybackSettings::fake(['completion_threshold' => 0.9]);

    $this->user = User::factory()->create();
    $this->video = Video::factory()->withDuration(600)->create();

    $this->actingAs($this->user);
});

it('stores the first position in the viewed group with the share watched', function () {
    watchVideo($this->video, $this->user, 150);

    $options = $this->user->groupFor(GroupType::Viewed)->getGroupable($this->video)->options;

    expect((float) $options['time'])->toBe(150.0)
        ->and((float) $options['progress'])->toBe(0.25);
});

it('keeps the stored position while playback moves less than 15 seconds', function () {
    watchVideo($this->video, $this->user, 100);
    watchVideo($this->video, $this->user, 110);

    expect(storedWatchTime($this->video, $this->user))->toBe(100.0);
});

it('stores the position again once playback moves 15 seconds or more', function () {
    watchVideo($this->video, $this->user, 100);
    watchVideo($this->video, $this->user, 110);
    watchVideo($this->video, $this->user, 115);

    expect(storedWatchTime($this->video, $this->user))->toBe(115.0);
});

it('stores the position as soon as the video counts as watched', function () {
    watchVideo($this->video, $this->user, 535);
    watchVideo($this->video, $this->user, 541);

    expect(storedWatchTime($this->video, $this->user))->toBe(541.0);
});

it('stores the position as soon as a watched video is rewound', function () {
    watchVideo($this->video, $this->user, 545);
    watchVideo($this->video, $this->user, 535);

    expect(storedWatchTime($this->video, $this->user))->toBe(535.0);
});

it('stores nothing for guests', function () {
    app(SyncVideoProgress::class)->handle(new VideoHasBeenViewedEvent($this->video, null, ['time' => 30]));

    expect($this->user->groupFor(GroupType::Viewed)->groupables()->count())->toBe(0);
});

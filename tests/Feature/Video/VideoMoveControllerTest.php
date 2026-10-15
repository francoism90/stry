<?php

declare(strict_types=1);

use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskDriver;
use Domain\Users\Models\User;
use Domain\Videos\Jobs\MoveVideo;
use Domain\Videos\Models\Video;
use Foundation\Settings\StorageSettings;
use Illuminate\Support\Facades\Bus;
use Modules\Web\Videos\Controllers\VideoMoveController;

beforeEach(function () {
    Bus::fake([MoveVideo::class]);

    $settings = app(StorageSettings::class);
    $settings->disks = [
        ...$settings->disks,
        new StorageDisk(name: 'archive', label: 'Archive', driver: DiskDriver::Local, path: '/mnt/archive', contents: ['videos']),
    ];
    $settings->save();
});

it('queues a move of the video to another video disk for admins', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->post(action(VideoMoveController::class, $video), ['disk' => 'archive']);

    $response->assertRedirect();
    Bus::assertDispatched(MoveVideo::class, fn (MoveVideo $job): bool => $job->video->is($video)
        && $job->user->is($user)
        && $job->disk === 'archive');
});

it('rejects a disk that does not hold videos', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->post(action(VideoMoveController::class, $video), ['disk' => 'exports']);

    $response->assertInvalid(['disk']);
    Bus::assertNotDispatched(MoveVideo::class);
});

it('forbids users who are not admins', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->post(action(VideoMoveController::class, $video), ['disk' => 'archive']);

    $response->assertForbidden();
    Bus::assertNotDispatched(MoveVideo::class);
});

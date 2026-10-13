<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Jobs\ExportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Modules\Web\Videos\Controllers\VideoExportController;

uses(RefreshDatabase::class);

it('queues an export of the video for admins', function () {
    Bus::fake([ExportVideo::class]);

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->post(action(VideoExportController::class, $video));

    $response->assertRedirect();
    Bus::assertDispatched(ExportVideo::class, fn (ExportVideo $job): bool => $job->video->is($video));
});

it('forbids users without update permission', function () {
    Bus::fake([ExportVideo::class]);

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $owner->getKey()]);

    $response = $this->actingAs($other)->post(action(VideoExportController::class, $video));

    $response->assertForbidden();
    Bus::assertNotDispatched(ExportVideo::class);
});

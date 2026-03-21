<?php

declare(strict_types=1);

use App\Api\Videos\Controllers\VideoTranscodedController;
use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;
use Domain\Videos\Jobs\ImportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('transcodes'));

it('imports all completed transcodes for a video', function () {
    Bus::fake();

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    Transcode::factory()->completed()->count(2)->for($video, 'transcodable')->create();

    $response = $this->actingAs($user)->post(action(VideoTranscodedController::class, $video));

    $response->assertRedirect();
    Bus::assertDispatchedTimes(ImportVideo::class, 2);
});

it('does not import non-completed transcodes', function () {
    Bus::fake();

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    Transcode::factory()->create(['transcodable_id' => $video->getKey()]); // pending
    Transcode::factory()->failed()->for($video, 'transcodable')->create();
    Transcode::factory()->imported()->for($video, 'transcodable')->create();

    $response = $this->actingAs($user)->post(action(VideoTranscodedController::class, $video));

    $response->assertRedirect();
    Bus::assertNotDispatched(ImportVideo::class);
});

it('forbids users without update permission', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $video = Video::factory()->create(['user_id' => $owner->getKey()]);

    $response = $this->actingAs($other)->post(action(VideoTranscodedController::class, $video));

    $response->assertForbidden();
});

it('redirects unauthenticated guests', function () {
    $video = Video::factory()->create();

    $response = $this->post(action(VideoTranscodedController::class, $video));

    $response->assertRedirect();
});

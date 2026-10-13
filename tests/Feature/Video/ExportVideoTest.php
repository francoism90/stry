<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Domain\Users\Models\User;
use Domain\Videos\Actions\ExportVideoMedia;
use Domain\Videos\Jobs\ExportVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\Notifications\VideoExportedNotification;
use Domain\Videos\Notifications\VideoExportFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function storeExportableMedia(Video $video): Media
{
    $media = $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 13,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    Storage::disk('media')->put($media->getPathRelativeToRoot(), 'clip-contents');

    return $media;
}

it('keeps the video after exporting by default', function () {
    Storage::fake('media');
    Storage::fake('exports');
    Notification::fake();

    $user = User::factory()->create();
    $video = Video::factory()->create();
    storeExportableMedia($video);

    (new ExportVideo($video, $user))->handle();

    expect($video->fresh()->trashed())->toBeFalse();
    Notification::assertSentTo($user, VideoExportedNotification::class, fn (VideoExportedNotification $notification): bool => $notification->deleted === false);
});

it('deletes the video after a successful export when requested', function () {
    Storage::fake('media');
    Storage::fake('exports');
    Notification::fake();

    $user = User::factory()->create();
    $video = Video::factory()->create();
    storeExportableMedia($video);

    (new ExportVideo($video, $user, true))->handle();

    Storage::disk('exports')->assertExists("{$video->getRouteKey()}/clips/clip.mp4");
    $this->assertSoftDeleted($video);
    Notification::assertSentTo($user, VideoExportedNotification::class, fn (VideoExportedNotification $notification): bool => $notification->deleted === true);
});

it('keeps the video and notifies the user when the export fails', function () {
    Storage::fake('media');
    Storage::fake('exports');
    Notification::fake();

    $user = User::factory()->create();
    $video = Video::factory()->create();
    storeExportableMedia($video);

    $this->mock(ExportVideoMedia::class)
        ->shouldReceive('handle')
        ->andThrow(new RuntimeException('Export failed.'));

    $job = new ExportVideo($video, $user, true);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class);
    $job->failed(new RuntimeException('Export failed.'));

    expect($video->fresh()->trashed())->toBeFalse();
    Notification::assertNotSentTo($user, VideoExportedNotification::class);
    Notification::assertSentTo($user, VideoExportFailedNotification::class);
});

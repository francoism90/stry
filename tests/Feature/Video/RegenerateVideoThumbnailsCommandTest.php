<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('conversions');
});

function createRegeneratedClip(Video $video, string $fileName): Domain\Media\Models\Media
{
    return $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => $fileName,
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => ['thumbnails' => ['disk' => 'conversions', 'sprites' => ['old.jpg'], 'vtt' => 'old.vtt', 'interval' => 5.0, 'count' => 2]],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('regenerates the thumbnails of every clip', function () {
    Media::fake(['short.mp4' => FakeProbe::video(duration: 10), 'long.mp4' => FakeProbe::video(duration: 600)]);
    $short = createRegeneratedClip(Video::factory()->create(), 'short.mp4');
    $long = createRegeneratedClip(Video::factory()->create(), 'long.mp4');

    $this->artisan('videos:thumbnails', ['--force' => true])->assertSuccessful();

    expect($short->fresh()->getThumbnails())->interval->toBe(1.0)->count->toBe(10)
        ->and($long->fresh()->getThumbnails())->interval->toBe(2.0);
});

it('regenerates only short clips or the given videos', function () {
    Media::fake(['short.mp4' => FakeProbe::video(duration: 10), 'long.mp4' => FakeProbe::video(duration: 600)]);
    $short = createRegeneratedClip(Video::factory()->create(), 'short.mp4');
    $long = createRegeneratedClip($longVideo = Video::factory()->create(), 'long.mp4');

    $this->artisan('videos:thumbnails', ['--shorter-than' => 60, '--force' => true])->assertSuccessful();

    expect($short->fresh()->getThumbnails()?->interval)->toBe(1.0)
        ->and($long->fresh()->getThumbnails()?->interval)->toBe(5.0);

    $this->artisan('videos:thumbnails', ['video' => [$longVideo->getKey()], '--force' => true])->assertSuccessful();

    expect($long->fresh()->getThumbnails()?->interval)->toBe(2.0);
});

it('asks before regenerating', function () {
    Media::fake(['short.mp4' => FakeProbe::video(duration: 10)]);
    $short = createRegeneratedClip(Video::factory()->create(), 'short.mp4');

    $this->artisan('videos:thumbnails')
        ->expectsConfirmation('Regenerate the thumbnails of 1 videos?', 'no')
        ->assertSuccessful();

    expect($short->fresh()->getThumbnails()?->interval)->toBe(5.0);
});

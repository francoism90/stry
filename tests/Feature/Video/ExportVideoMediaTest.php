<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Domain\Videos\Actions\ExportVideoMedia;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createStoredMedia(Video $video, string $collection, string $fileName, string $contents): Media
{
    $media = $video->media()->create([
        'collection_name' => $collection,
        'name' => pathinfo($fileName, PATHINFO_FILENAME),
        'file_name' => $fileName,
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => strlen($contents),
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    Storage::disk('media')->put($media->getPathRelativeToRoot(), $contents);

    return $media;
}

it('copies every media file of the video into its export directory', function () {
    Storage::fake('media');
    Storage::fake('exports');

    $video = Video::factory()->create();
    createStoredMedia($video, 'clips', 'clip.mp4', 'clip-contents');
    createStoredMedia($video, 'captions', 'clip.en.vtt', 'caption-contents');

    app(ExportVideoMedia::class)->handle($video);

    $directory = $video->getRouteKey();
    Storage::disk('exports')->assertExists("{$directory}/clips/clip.mp4");
    expect(Storage::disk('exports')->get("{$directory}/clips/clip.mp4"))->toBe('clip-contents')
        ->and(Storage::disk('exports')->get("{$directory}/captions/clip.en.vtt"))->toBe('caption-contents');
});

it('keeps media with the same file name apart', function () {
    Storage::fake('media');
    Storage::fake('exports');

    $video = Video::factory()->create();
    createStoredMedia($video, 'clips', 'clip.mp4', 'first');
    $second = createStoredMedia($video, 'clips', 'clip.mp4', 'second');

    app(ExportVideoMedia::class)->handle($video);

    $directory = $video->getRouteKey();
    expect(Storage::disk('exports')->get("{$directory}/clips/clip.mp4"))->toBe('first')
        ->and(Storage::disk('exports')->get("{$directory}/clips/{$second->getKey()}-clip.mp4"))->toBe('second');
});

it('replaces a previous export of the video', function () {
    Storage::fake('media');
    Storage::fake('exports');

    $video = Video::factory()->create();
    createStoredMedia($video, 'clips', 'clip.mp4', 'clip-contents');
    Storage::disk('exports')->put("{$video->getRouteKey()}/clips/stale.mp4", 'stale');

    app(ExportVideoMedia::class)->handle($video);

    Storage::disk('exports')->assertMissing("{$video->getRouteKey()}/clips/stale.mp4");
    Storage::disk('exports')->assertExists("{$video->getRouteKey()}/clips/clip.mp4");
});

it('leaves the exports of other videos untouched', function () {
    Storage::fake('media');
    Storage::fake('exports');

    $video = Video::factory()->create();
    $other = Video::factory()->create();
    Storage::disk('exports')->put("{$other->getRouteKey()}/clips/other.mp4", 'other');

    app(ExportVideoMedia::class)->handle($video);

    Storage::disk('exports')->assertExists("{$other->getRouteKey()}/clips/other.mp4");
});

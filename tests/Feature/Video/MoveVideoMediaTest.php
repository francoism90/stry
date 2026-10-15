<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Domain\Videos\Actions\MoveVideoMedia;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('archive');
});

function createMovableMedia(Video $video, string $collection, string $fileName, string $contents): Media
{
    $media = $video->media()->create([
        'collection_name' => $collection,
        'name' => pathinfo($fileName, PATHINFO_FILENAME),
        'file_name' => $fileName,
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'conversions_disk' => 'conversions',
        'size' => strlen($contents),
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    Storage::disk('media')->put($media->getPathRelativeToRoot(), $contents);

    return $media;
}

it('moves the clips and captions to the other disk and keeps their records', function () {
    Event::fake([MediaHasBeenAddedEvent::class]);

    $video = Video::factory()->create();
    $clip = createMovableMedia($video, 'clips', 'clip.mp4', 'clip-contents');
    $caption = createMovableMedia($video, 'captions', 'clip.en.vtt', 'caption-contents');

    app(MoveVideoMedia::class)->handle($video, 'archive');

    $movedClip = Media::findOrFail($clip->getKey());
    $movedCaption = Media::findOrFail($caption->getKey());

    expect($movedClip->disk)->toBe('archive')
        ->and($movedClip->conversions_disk)->toBe('conversions')
        ->and($movedCaption->disk)->toBe('archive')
        ->and(Storage::disk('archive')->get($clip->getPathRelativeToRoot()))->toBe('clip-contents')
        ->and(Storage::disk('archive')->get($caption->getPathRelativeToRoot()))->toBe('caption-contents');

    Storage::disk('media')->assertMissing($clip->getPathRelativeToRoot());
    Storage::disk('media')->assertMissing($caption->getPathRelativeToRoot());
    Event::assertNotDispatched(MediaHasBeenAddedEvent::class);
});

it('leaves other collections on their disk', function () {
    $video = Video::factory()->create();
    $reel = createMovableMedia($video, 'reels', 'reel.mp4', 'reel-contents');

    app(MoveVideoMedia::class)->handle($video, 'archive');

    expect($reel->refresh()->disk)->toBe('media');
    Storage::disk('media')->assertExists($reel->getPathRelativeToRoot());
});

it('keeps the original when the moved file does not match it', function () {
    $video = Video::factory()->create();
    $clip = createMovableMedia($video, 'clips', 'clip.mp4', 'clip-contents');

    $archive = Mockery::mock(Storage::disk('archive'))->makePartial();
    $archive->shouldReceive('size')->andReturn(1);
    Storage::set('archive', $archive);

    expect(fn () => app(MoveVideoMedia::class)->handle($video, 'archive'))->toThrow(RuntimeException::class)
        ->and($clip->refresh()->disk)->toBe('media');

    Storage::disk('media')->assertExists($clip->getPathRelativeToRoot());
});

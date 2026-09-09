<?php

declare(strict_types=1);

use Domain\Media\Actions\ExtractMediaCaptions;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\ExtractVideoCaptions;
use Domain\Videos\Settings\ProcessingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Storage::fake(Transcode::getDestinationDisk());
});

function createVideoClipForCaptions(Video $video): void
{
    $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('attaches the extracted captions to the captions collection', function () {
    $video = Video::factory()->create();
    createVideoClipForCaptions($video);

    Storage::disk(Transcode::getDestinationDisk())->put('caption.vtt', "WEBVTT\n");

    app()->instance(ExtractMediaCaptions::class, new class
    {
        public function handle(): Collection
        {
            return Collection::make(['caption.vtt']);
        }
    });

    app(ExtractVideoCaptions::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->getMedia('captions'))->toHaveCount(1);
});

it('skips extraction when the video already has captions', function () {
    $video = Video::factory()->create();
    createVideoClipForCaptions($video);

    $video->media()->create([
        'collection_name' => 'captions',
        'name' => 'caption',
        'file_name' => 'caption.vtt',
        'mime_type' => 'text/vtt',
        'disk' => Transcode::getDestinationDisk(),
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    app()->instance(ExtractMediaCaptions::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaCaptions should not be called.');
        }
    });

    app(ExtractVideoCaptions::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->getMedia('captions'))->toHaveCount(1);
});

it('skips extraction when the video has no clips', function () {
    $video = Video::factory()->create();

    app()->instance(ExtractMediaCaptions::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaCaptions should not be called.');
        }
    });

    app(ExtractVideoCaptions::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->hasMedia('captions'))->toBeFalse();
});

it('skips extraction when auto-extraction is disabled', function () {
    $video = Video::factory()->create();
    createVideoClipForCaptions($video);

    app(ProcessingSettings::class)->fill(['extract_captions' => false])->save();

    app()->instance(ExtractMediaCaptions::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaCaptions should not be called.');
        }
    });

    app(ExtractVideoCaptions::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->hasMedia('captions'))->toBeFalse();
});

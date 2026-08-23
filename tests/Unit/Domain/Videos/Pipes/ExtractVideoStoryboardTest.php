<?php

declare(strict_types=1);

use Domain\Media\Actions\GenerateMediaStoryboard;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\ExtractVideoStoryboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Storage::fake(Transcode::getDestinationDisk());
});

function createVideoClip(Video $video): void
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

it('attaches the generated sprite and vtt to the storyboards collection', function () {
    $video = Video::factory()->create();
    createVideoClip($video);

    Storage::disk(Transcode::getDestinationDisk())->put('storyboard.jpg', 'fake-image-bytes');
    Storage::disk(Transcode::getDestinationDisk())->put('storyboard.vtt', "WEBVTT\n");

    app()->instance(GenerateMediaStoryboard::class, new class
    {
        public function handle(): array
        {
            return ['image' => 'storyboard.jpg', 'vtt' => 'storyboard.vtt'];
        }
    });

    app(ExtractVideoStoryboard::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->getMedia('storyboards'))->toHaveCount(2);
});

it('skips generation when the video already has a storyboard', function () {
    $video = Video::factory()->create();
    createVideoClip($video);

    $video->media()->create([
        'collection_name' => 'storyboards',
        'name' => 'storyboard',
        'file_name' => 'storyboard.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    app()->instance(GenerateMediaStoryboard::class, new class
    {
        public function handle(): array
        {
            throw new RuntimeException('GenerateMediaStoryboard should not be called.');
        }
    });

    app(ExtractVideoStoryboard::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->getMedia('storyboards'))->toHaveCount(1);
});

it('skips generation when the video has no clips', function () {
    $video = Video::factory()->create();

    app()->instance(GenerateMediaStoryboard::class, new class
    {
        public function handle(): array
        {
            throw new RuntimeException('GenerateMediaStoryboard should not be called.');
        }
    });

    app(ExtractVideoStoryboard::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->hasMedia('storyboards'))->toBeFalse();
});

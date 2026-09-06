<?php

declare(strict_types=1);

use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\GenerateVideoChapters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('conversions'));

it('generates and attaches a chapters vtt file when the video has chapters', function () {
    $video = Video::factory()->create();
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

    app(GenerateVideoChapters::class)->handle($video, fn (Video $video) => $video);

    $media = $video->fresh()->getMedia('chapters');

    expect($media)->toHaveCount(1)
        ->and($media->first()->mime_type)->toBe('text/vtt');
});

it('replaces the previous chapters file when regenerated', function () {
    $video = Video::factory()->create();
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

    app(GenerateVideoChapters::class)->handle($video, fn (Video $video) => $video);
    app(GenerateVideoChapters::class)->handle($video->fresh(), fn (Video $video) => $video);

    expect($video->fresh()->getMedia('chapters'))->toHaveCount(1);
});

it('clears the chapters file when the video has no chapters', function () {
    $video = Video::factory()->create();
    $video->media()->create([
        'collection_name' => 'chapters',
        'name' => 'chapters',
        'file_name' => 'chapters.vtt',
        'mime_type' => 'text/vtt',
        'disk' => 'conversions',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    app(GenerateVideoChapters::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->hasMedia('chapters'))->toBeFalse();
});

<?php

declare(strict_types=1);

use Domain\Chapters\Models\Chapter;
use Domain\Media\Actions\ExtractMediaChapters;
use Domain\Videos\Models\Video;
use Domain\Videos\Pipes\ExtractVideoChapters;
use Domain\Videos\Settings\ProcessingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('media'));

it('creates chapters from the extracted media chapters', function () {
    $video = Video::factory()->create();
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

    app()->instance(ExtractMediaChapters::class, new class
    {
        public function handle(): Collection
        {
            return Collection::make([
                ['label' => 'Introduction', 'start_time' => 0.0, 'end_time' => 30.0],
                ['label' => 'Main Content', 'start_time' => 30.0, 'end_time' => 120.0],
            ]);
        }
    });

    app(ExtractVideoChapters::class)->handle($video, fn (Video $video) => $video);

    $chapters = $video->fresh()->chapters;

    expect($chapters)->toHaveCount(2)
        ->and($chapters->pluck('label')->all())->toBe(['Introduction', 'Main Content']);
});

it('skips extraction when the video already has chapters', function () {
    $video = Video::factory()->create();
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

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

    app()->instance(ExtractMediaChapters::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaChapters should not be called.');
        }
    });

    app(ExtractVideoChapters::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->chapters)->toHaveCount(1);
});

it('skips extraction when the video has no clips', function () {
    $video = Video::factory()->create();

    app()->instance(ExtractMediaChapters::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaChapters should not be called.');
        }
    });

    app(ExtractVideoChapters::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->chapters)->toHaveCount(0);
});

it('skips extraction when auto-extraction is disabled', function () {
    $video = Video::factory()->create();
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

    app(ProcessingSettings::class)->fill(['extract_chapters' => false])->save();

    app()->instance(ExtractMediaChapters::class, new class
    {
        public function handle(): Collection
        {
            throw new RuntimeException('ExtractMediaChapters should not be called.');
        }
    });

    app(ExtractVideoChapters::class)->handle($video, fn (Video $video) => $video);

    expect($video->fresh()->chapters)->toHaveCount(0);
});

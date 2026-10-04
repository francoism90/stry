<?php

declare(strict_types=1);

use Domain\Media\Actions\ExtractMediaChapters;
use Domain\Media\Models\Media as MediaModel;
use Domain\Videos\Models\Video;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('reads the titled chapters of a clip from its probe', function () {
    Storage::fake('media');
    Media::fake(['clip.mp4' => [...FakeProbe::video(duration: 120), 'chapters' => [
        ['start_time' => '0.000000', 'end_time' => '30.000000', 'tags' => ['title' => 'Introduction']],
        ['start_time' => '30.000000', 'end_time' => '30.000000', 'tags' => ['title' => 'Empty']],
        ['start_time' => '30.000000', 'end_time' => '90.000000'],
        ['start_time' => '90.000000', 'end_time' => '120.000000', 'tags' => ['title' => 'Credits']],
    ]]]);

    /** @var MediaModel $clip */
    $clip = Video::factory()->create()->media()->create([
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

    expect(app(ExtractMediaChapters::class)->handle($clip)->all())->toBe([
        ['label' => 'Introduction', 'start_time' => 0.0, 'end_time' => 30.0],
        ['label' => 'Credits', 'start_time' => 90.0, 'end_time' => 120.0],
    ]);
});

<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createClipWithStream(array $stream = [])
{
    return Video::factory()->create()->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [
            'streams' => [
                ['codec_type' => 'video', ...$stream],
                ['codec_type' => 'audio'],
            ],
        ],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('returns the uppercased codec name of the video stream', function () {
    $media = createClipWithStream(['codec_name' => 'hevc']);

    expect($media->codec)->toBe('HEVC');
});

it('returns a null codec when the video stream has none', function () {
    $media = createClipWithStream();

    expect($media->codec)->toBeNull();
});

it('formats the resolution of the video stream', function () {
    $media = createClipWithStream(['width' => 1920, 'height' => 1080]);

    expect($media->resolution)->toBe('1920×1080');
});

it('returns a null resolution when the video stream is missing dimensions', function () {
    $media = createClipWithStream(['width' => 1920]);

    expect($media->resolution)->toBeNull();
});

it('formats the bitrate of the video stream in kbps', function () {
    $media = createClipWithStream(['bit_rate' => 6_002_000]);

    expect($media->bitrate)->toBe('6002kbps');
});

it('returns a null bitrate when the video stream has none', function () {
    $media = createClipWithStream();

    expect($media->bitrate)->toBeNull();
});

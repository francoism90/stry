<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Storage;

it('deletes the stored storyboards with their files, and keeps other media', function () {
    Storage::fake('conversions');

    $video = Video::factory()->create();
    $storyboard = $video->media()->create([
        'collection_name' => 'storyboards',
        'name' => 'storyboard',
        'file_name' => 'storyboard.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'conversions',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
    $chapters = $video->media()->create([...$storyboard->only(['disk', 'size', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images']), 'collection_name' => 'chapters', 'name' => 'chapters', 'file_name' => 'chapters.vtt', 'mime_type' => 'text/vtt']);
    Storage::disk('conversions')->put($storyboard->getPathRelativeToRoot(), 'sprite');

    (require database_path('migrations/2026_10_05_120602_delete_video_storyboards.php'))->up();

    expect($video->media()->pluck('id')->all())->toBe([$chapters->getKey()])
        ->and(Storage::disk('conversions')->exists($storyboard->getPathRelativeToRoot()))->toBeFalse();
});

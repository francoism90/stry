<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Storage;

it('deletes the renditions encoded next to clips, and keeps the clips', function () {
    Storage::fake('media');

    $clip = Video::factory()->create()->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => ['renditions' => ['heights' => [720], 'paths' => ['1/renditions/720p.mp4']], 'thumbnails' => []],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
    Storage::disk('media')->put('1/renditions/720p.mp4', 'video');
    Storage::disk('media')->put($clip->getPathRelativeToRoot(), 'video');

    (require database_path('migrations/2026_10_05_170000_delete_clip_renditions.php'))->up();

    expect($clip->fresh()->hasCustomProperty('renditions'))->toBeFalse()
        ->and($clip->fresh()->hasCustomProperty('thumbnails'))->toBeTrue();
    Storage::disk('media')->assertMissing('1/renditions/720p.mp4');
    Storage::disk('media')->assertExists($clip->getPathRelativeToRoot());
});

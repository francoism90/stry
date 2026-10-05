<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateClipRenditions;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 20, width: 1280, height: 720)]);
});

function createRenditionsClip(): Domain\Media\Models\Media
{
    return Video::factory()->create()->media()->create([
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

it('encodes the chosen renditions smaller than the clip next to it, aligned to its keyframes', function () {
    PlaybackSettings::fake(['renditions' => [1080, 720, 480]]);
    $clip = createRenditionsClip();
    $path = str_replace('{height}', '480', $clip->getRenditionPathPattern());

    app(CreateClipRenditions::class)->handle($clip);

    expect($clip->fresh()->getRenditionHeights())->toBe([1080, 720, 480])
        ->and($clip->fresh()->getRenditionPaths())->toBe([$path]);
    Storage::disk('media')->assertExists($path);
    Media::assertRan(Executable::FFMpeg, fn (array $arguments) => in_array('-force_key_frames', $arguments, true)
        && in_array('scale=-2:480', $arguments, true)
        && ! in_array('scale=-2:720', $arguments, true));
});

it('deletes renditions that are no longer chosen', function () {
    PlaybackSettings::fake(['renditions' => [480]]);
    $clip = createRenditionsClip();
    app(CreateClipRenditions::class)->handle($clip);
    $path = $clip->fresh()->getRenditionPaths()[0];

    PlaybackSettings::fake(['renditions' => []]);
    app(CreateClipRenditions::class)->handle($clip->fresh());

    expect($clip->fresh()->getRenditionPaths())->toBe([])
        ->and($clip->fresh()->getRenditionHeights())->toBe([]);
    Storage::disk('media')->assertMissing($path);
});

it('deletes the renditions with the clip', function () {
    PlaybackSettings::fake(['renditions' => [480]]);
    $clip = createRenditionsClip();
    app(CreateClipRenditions::class)->handle($clip);
    $path = $clip->fresh()->getRenditionPaths()[0];

    $clip->fresh()->delete();

    Storage::disk('media')->assertMissing($path);
});

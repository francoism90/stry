<?php

declare(strict_types=1);

use Domain\Media\Actions\ExtractMediaCaptions;
use Domain\Videos\Models\Video;
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createCaptionsClip(): Domain\Media\Models\Media
{
    return Video::factory()->create()->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mkv',
        'mime_type' => 'video/x-matroska',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('converts each subtitle stream to webvtt on the transcode disk', function () {
    Storage::fake('media');
    Storage::fake('cache');
    Media::fake(['clip.mkv' => FakeProbe::video(subtitles: ['eng', 'nld'])]);

    $clip = createCaptionsClip();

    $paths = app(ExtractMediaCaptions::class)->handle($clip);

    expect($paths->all())->toBe(["{$clip->uuid}_2_eng.vtt", "{$clip->uuid}_3_nld.vtt"]);

    Media::assertSaved("{$clip->uuid}_2_eng.vtt", 'cache');
    Media::assertRan(Executable::FFMpeg, fn (array $arguments) => in_array('0:3', $arguments, true) && in_array('webvtt', $arguments, true));
});

it('skips subtitle streams that fail to convert', function () {
    Storage::fake('media');
    Storage::fake('cache');
    Media::fake(['clip.mkv' => FakeProbe::video(subtitles: ['eng', 'nld'])])
        ->failNext(Executable::FFMpeg, 'Subtitle encoding currently only possible from text to text');

    $clip = createCaptionsClip();

    expect(app(ExtractMediaCaptions::class)->handle($clip)->all())->toBe(["{$clip->uuid}_3_nld.vtt"]);
});

it('extracts nothing without subtitle streams', function () {
    Storage::fake('media');
    Media::fake(['clip.mkv' => FakeProbe::video()]);

    expect(app(ExtractMediaCaptions::class)->handle(createCaptionsClip()))->toBeEmpty();

    Media::assertNotRan(Executable::FFMpeg);
});

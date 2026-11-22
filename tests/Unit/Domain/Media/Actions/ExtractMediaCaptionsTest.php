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

it('keeps a language tag that is not a code out of the path', function (string $language) {
    Storage::fake('media');
    Storage::fake('cache');
    Media::fake(['clip.mkv' => FakeProbe::video(subtitles: [$language])]);

    $clip = createCaptionsClip();

    expect(app(ExtractMediaCaptions::class)->handle($clip)->all())->toBe(["{$clip->uuid}_2_und.vtt"]);

    Media::assertSaved("{$clip->uuid}_2_und.vtt", 'cache');
})->with([
    'forward slashes' => 'x/../../escaped',
    'backslashes' => 'x/..\\..\\escaped',
    'line break' => "eng\n",
    'free text' => 'English (SDH)',
    'empty' => '',
]);

it('keeps a language code with a region', function () {
    Storage::fake('media');
    Storage::fake('cache');
    Media::fake(['clip.mkv' => FakeProbe::video(subtitles: ['pt-BR'])]);

    $clip = createCaptionsClip();

    expect(app(ExtractMediaCaptions::class)->handle($clip)->all())->toBe(["{$clip->uuid}_2_pt-BR.vtt"]);
});

it('extracts nothing without subtitle streams', function () {
    Storage::fake('media');
    Media::fake(['clip.mkv' => FakeProbe::video()]);

    expect(app(ExtractMediaCaptions::class)->handle(createCaptionsClip()))->toBeEmpty();

    Media::assertNotRan(Executable::FFMpeg);
});

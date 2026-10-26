<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateVideoDirectStream;
use Domain\Videos\Jobs\WarmVideoStream;
use Domain\Videos\Models\Video;
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('cache');

    Media::fake(['clip.mp4' => FakeProbe::video(duration: 13)]);
});

it('stores the probe and keyframe index of the clip', function () {
    $video = Video::factory()->create();
    $clip = $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 5,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
    Storage::disk('media')->put($clip->getPathRelativeToRoot(), 'video');

    (new WarmVideoStream($video))->handle(app(CreateVideoDirectStream::class));

    expect(DB::table('media_indexes')->where('path', $clip->getPathRelativeToRoot())->pluck('type')->sort()->values()->all())
        ->toBe(['keyframes', 'probe']);
});

it('does nothing for a video without a clip', function () {
    (new WarmVideoStream(Video::factory()->create()))->handle(app(CreateVideoDirectStream::class));

    Media::assertNotRan(Executable::FFProbe);
});

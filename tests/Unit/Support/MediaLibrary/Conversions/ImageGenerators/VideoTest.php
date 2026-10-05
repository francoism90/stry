<?php

declare(strict_types=1);

use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;
use Spatie\MediaLibrary\Conversions\Conversion;
use Support\MediaLibrary\Conversions\ImageGenerators\Video;

beforeEach(function () {
    $this->directory = sys_get_temp_dir().'/'.uniqid('video-generator-');

    mkdir($this->directory);
    file_put_contents("{$this->directory}/clip.mp4", 'video');
});

afterEach(function () {
    array_map(unlink(...), glob("{$this->directory}/*") ?: []);
    rmdir($this->directory);
});

it('grabs a frame from the middle of the video', function () {
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 120)]);

    $image = (new Video)->convert("{$this->directory}/clip.mp4");

    expect($image)->toBe("{$this->directory}/clip.jpg")
        ->and(file_exists($image))->toBeTrue();

    Media::assertRan(Executable::FFMpeg, fn (array $arguments) => in_array('60', $arguments, true));
});

it('grabs the frame at the second of the conversion, within the duration', function () {
    Media::fake(['clip.mp4' => FakeProbe::video(duration: 10)]);

    (new Video)->convert("{$this->directory}/clip.mp4", (new Conversion('thumb'))->extractVideoFrameAtSecond(30));

    expect(collect(Media::commands(Executable::FFMpeg))->flatten()->all())->toContain('10');
});

it('makes no image from media without video', function () {
    Media::fake(['clip.mp4' => FakeProbe::audio()]);

    expect((new Video)->convert("{$this->directory}/clip.mp4"))->toBeNull();

    Media::assertNotRan(Executable::FFMpeg);
});

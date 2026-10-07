<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateNewVideoTranscode;
use Domain\Videos\Jobs\TranscodeVideo;
use Domain\Videos\Models\Video;
use Foxws\AbAv1\AbAv1Executable;
use Foxws\AbAv1\Testing\FakeAbAv1;
use Foxws\Media\Exceptions\ProcessFailedException;
use Foxws\Media\Facades\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('transcodes');
    config(['videos.transcode_disk' => 'transcodes']);

    $this->fake = FakeAbAv1::respond(Media::fake());
});

function createTranscodeClip(Video $video): Domain\Media\Models\Media
{
    return $video->media()->create([
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

it('encodes each clip to AV1 with verification and saves it as a completed transcode', function () {
    $video = Video::factory()->create();
    createTranscodeClip($video);

    $transcode = app(CreateNewVideoTranscode::class)->handle($video)->first();

    expect($transcode->isCompleted())->toBeTrue()
        ->and($transcode->getOutputPath())->toEndWith('clip.mp4');
    Storage::disk('transcodes')->assertExists($transcode->getOutputPath());
    $this->fake->assertRan(AbAv1Executable::AbAv1, fn (array $arguments) => $arguments[0] === 'auto-encode'
        && in_array('--verify', $arguments, true)
        && in_array('--fail-fast', $arguments, true));
});

it('logs the progress ab-av1 reports while it encodes', function () {
    $video = Video::factory()->create();
    createTranscodeClip($video);
    FakeAbAv1::respond($this->fake, errorOutput: "[2026-10-06T12:00:16Z INFO  ab_av1::command::encode] 25%, 24.5 fps, eta 45 seconds\n");
    Log::spy();

    $transcode = app(CreateNewVideoTranscode::class)->handle($video)->first();

    Log::shouldHaveReceived('info')->with('Transcoding video', ['transcode_id' => $transcode->getKey(), 'percentage' => 25.0, 'remaining' => 45.0, 'fps' => 24.5])->once();
    Log::shouldHaveReceived('info')->with('Transcoding video', Mockery::on(fn (array $context) => $context['percentage'] === 100.0))->once();
});

it('marks the transcode as failed when ab-av1 fails', function () {
    $video = Video::factory()->create();
    createTranscodeClip($video);
    $this->fake->failNext(AbAv1Executable::AbAv1, 'Error: ffmpeg encode exit code 1');

    expect(fn () => app(CreateNewVideoTranscode::class)->handle($video))->toThrow(ProcessFailedException::class);

    $transcode = $video->transcodes()->first();

    expect($transcode->isFailed())->toBeTrue()
        ->and($transcode->error_message)->toContain('ab-av1 exited with code 1');
});

it('stops ab-av1 before the transcode job times out', function () {
    $jobTimeout = new ReflectionClass(TranscodeVideo::class)->getDefaultProperties()['timeout'];

    expect(config('ab-av1.timeout'))->toBeLessThan($jobTimeout);
});

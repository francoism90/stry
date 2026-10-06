---
name: laravel-ab-av1-development
description: Encode video to AV1 at a target quality with foxws/laravel-ab-av1 (ab-av1) on top of foxws/laravel-media, including auto-encode from and to Laravel disks, fixed-CRF encodes, CRF search, sample encodes, VMAF/XPSNR comparisons, hardware encoders, video filters, verifying the result and faking ab-av1 in tests. Use when working with $opener->abAv1(), Foxws\AbAv1 classes, config/ab-av1.php, or transcoding uploaded video before streaming it.
---

# Encoding with laravel-ab-av1

`foxws/laravel-ab-av1` is an add-on for `foxws/laravel-media`. It runs the [ab-av1](https://github.com/alexheretic/ab-av1) binary, which calls FFmpeg. Instead of choosing a CRF, you set a quality target (VMAF, or XPSNR), and ab-av1 test-encodes samples to find the CRF that reaches it with the smallest file. Opening media, disks, temporary files, the process runner, uploads, events and fakes all come from laravel-media; activate `laravel-media-development` for those.

## Encoding flow

```php
use Foxws\AbAv1\AbAv1Builder;
use Foxws\AbAv1\AbAv1Result;
use Foxws\Media\Facades\Media;

$result = Media::fromDisk('media')
    ->open('videos/clip.mp4')
    ->abAv1()
    ->withPreset(6)
    ->withMinVMAF(95)
    ->withVerify()
    ->withFailFast()
    ->toDisk('s3')                       // defaults to the source disk
    ->withVisibility('private')
    ->withContext(['video_id' => $video->id])
    ->afterSaving(fn (AbAv1Builder $builder, AbAv1Result $result) => $video->markAsEncoded($result->crf))
    ->save("encoded/{$video->id}.mp4");

$result->crf;      // float, e.g. 30.25 (svt-av1 uses quarter steps)
$result->vmaf;     // e.g. 95.1
$result->path();   // the saved path on the target disk
```

- `save($path)` runs `ab-av1 auto-encode`, or `encode` after `withCRF()`, into a laravel-media temporary directory, then moves or uploads the file. It dispatches laravel-media's `ExportCompleted`/`ExportFailed` with the `withContext()` data.
- Don't call any cleanup: laravel-media deletes temporary files (including downloaded remote inputs and ab-av1's samples) after every queue job and request.
- The preset and VMAF target have config defaults (`preset`, `min_vmaf`), so they only need setting to override them. `withMinXPSNR()` replaces the VMAF target, and the other way around.
- `withVerify()` and `withFailFast()` need ab-av1 v0.11.7 or later. They catch damaged or truncated encodes that FFmpeg reports as successful.

## Other commands

| Method | ab-av1 command | Needs |
| --- | --- | --- |
| `crfSearch()` | `crf-search` | a quality target (config default) |
| `sampleEncode()` | `sample-encode` | `withCRF()` |
| `vmaf()` / `xpsnr()` | `vmaf` / `xpsnr` | two opened files: `open($original, $encoded)` |

All return an `AbAv1Result` (`crf`, `vmaf`, `xpsnr`, `predictedSize` in bytes, `predictedPercent`, `predictedTime` in seconds, `export`, `output`). Each command only gets the options it accepts. `command()` returns the command line without running it.

## Encoder settings

- svt-av1 (`libsvtav1`) is the default. Presets go from 0 (slowest, smallest) to 13 (fastest). CRF goes from 0 to 70; lower is better quality.
- Hardware encoding: `->withEncoder('av1_vaapi')->withFFmpegOptions(['hwaccel' => 'vaapi', 'hwaccel_output_format' => 'vaapi'])`.
- `withEncoderArgs('key=value', ...)` adds `--enc`, `withPixelFormat(PixelFormat::Yuv420p10le)` sets `--pix-format`, and `withOption('keyint', '10s')` passes any other option (`true` for flags, `null` removes).
- Video filters use laravel-media's filter classes: `->addFilter(Scale::to(height: 720))` or `->withVideoFilter('scale=1280:-2')`, passed as `--vfilter`. Audio filters throw.
- `withMaxEncodedPercent()` fails the search when the result would be larger than that percentage of the input, which avoids re-encoding sources that are already small.
- ab-av1 runs the ffmpeg and ffprobe laravel-media is configured with (`MEDIA_FFMPEG_PATH`, `MEDIA_FFPROBE_PATH`): their directories go first in its `PATH`.

## Queues and errors

- Encodes are slow: often longer than the video itself without hardware encoding. Run them in a queued job, and give long encodes `->timeout($seconds)` below the job's `$timeout` (default: `ab-av1.timeout`).
- ab-av1 failures throw `Foxws\Media\Exceptions\ProcessFailedException`; use `isRetryable()` to choose between `release()` and `fail()`. A missing binary throws `ExecutableNotFoundException`.
- `Foxws\AbAv1\Exceptions\AbAv1Exception`: `sampleEncode()` without a CRF, a comparison with fewer than two files, or an encode that wrote no file. Out-of-range values throw `InvalidArgumentException`.
- `onProgress(fn (Progress $progress) => ...)` and laravel-media's `ProgressReported` event (with the context) report the whole-file encode of `save()`; return `false` to cancel. ab-av1 logs progress after 16, 32, 64 seconds and so on, so updates are few, followed by a finished one at 100%. The CRF search reports none.

## Configuration

Publish with `php artisan vendor:publish --tag=ab-av1-config`. `php artisan media:info` lists the ab-av1 binary next to ffmpeg and ffprobe.

| Key | Purpose |
| --- | --- |
| `binary` | Path to the `ab-av1` binary (default: from `PATH`) |
| `timeout` | Process timeout in seconds |
| `preset`, `min_vmaf`, `max_encoded_percent`, `samples` | Defaults for every encode |
| `encoder`, `encoder_args`, `pix_format`, `video_filter`, `ffmpeg_input_options` | Encoder and FFmpeg defaults |

Temporary files and logging are configured in laravel-media's `config/media.php`.

## Testing

Never run the real binary in tests. Fake laravel-media and answer ab-av1 with `FakeAbAv1`:

```php
use Foxws\AbAv1\AbAv1Executable;
use Foxws\AbAv1\Testing\FakeAbAv1;
use Foxws\Media\Facades\Media;

Storage::fake('media');
$fake = FakeAbAv1::respond(Media::fake(), crf: 30.25, score: 95.1);

// ... run the code under test

$fake->assertRan(AbAv1Executable::AbAv1, fn (array $arguments) => $arguments[0] === 'auto-encode');
Storage::disk('media')->assertExists('encoded/1.mp4');

$fake->failNext(AbAv1Executable::AbAv1, 'Error: ffmpeg encode exit code 1'); // test failures
```

`FakeAbAv1::respond(..., errorOutput: "[… INFO  ab_av1::command::encode] 50%, 24 fps, eta 1 minute\n")` streams progress lines to `onProgress()`.

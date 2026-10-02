---
name: laravel-ab-av1-development
description: Encode video to AV1 at a target quality with foxws/laravel-ab-av1 (ab-av1), including auto-encode from and to Laravel disks, CRF search, sample encodes, VMAF/XPSNR comparisons, hardware encoders and verifying the result. Use when working with the AbAv1 facade, Foxws\AbAv1 classes, config/ab-av1.php, or transcoding uploaded video before packaging it.
---

# Encoding with laravel-ab-av1

`foxws/laravel-ab-av1` wraps the [ab-av1](https://github.com/alexheretic/ab-av1) binary, which calls FFmpeg. Instead of choosing a CRF, you set a quality target (VMAF, or XPSNR), and ab-av1 test-encodes samples to find the CRF that reaches it with the smallest file. It encodes; it doesn't package. To turn the result into HLS or DASH, use `foxws/laravel-shaka` or `foxws/laravel-streamer` afterwards.

## Encoding flow

```php
use Foxws\AbAv1\Facades\AbAv1;

$encoder = AbAv1::fromDisk('media')->open('videos/clip.mp4');

try {
    $encoder
        ->withPreset(6)
        ->withMinVMAF(95)
        ->withVerify()
        ->withFailFast()
        ->export()
        ->toDisk('s3')
        ->toPath("encoded/{$video->getKey()}.mp4")
        ->withVisibility('private')
        ->afterSaving(fn ($exporter, $result) => $video->markAsEncoded($result->getCRFUsed()))
        ->save();
} finally {
    $encoder->cleanupTemporaryFiles();
}
```

- `save()` runs `ab-av1 auto-encode`, writes to a temporary file, and copies it to the target disk. Input on a remote disk is downloaded to `temporary_files_root` first, so that root needs room for the input and the output.
- Always call `cleanupTemporaryFiles()` in `finally`. Encodes run in long-lived queue workers, and failed jobs would otherwise leave large files behind.
- `save()` needs a preset and a quality target. Both have config defaults (`preset`, `min_vmaf`), so they only need setting to override them.
- Never start a chain with `AbAv1::encode()`: `encode()` runs a fixed-CRF encode immediately. Start with `fromDisk()->open()` or `withInput($localPath)`.
- `afterSaving()` callbacks receive the exporter and the `EncodingResult`.
- `withVerify()` and `withFailFast()` need ab-av1 v0.11.7 or later. They catch damaged or truncated encodes that FFmpeg reports as successful.

## Other commands

Each returns an `EncodingResult` and needs an input (`withInput()` or `open()`):

| Method | ab-av1 command | Needs |
| --- | --- | --- |
| `autoEncode()` | `auto-encode` | preset, `withMinVMAF()` or `withMinXPSNR()`, `withOutput()` |
| `crfSearch()` | `crf-search` | preset, quality target |
| `sampleEncode()` | `sample-encode` | preset, `withCRF()` |
| `encode()` | `encode` | preset, `withCRF()`, `withOutput()` |
| `vmaf($original, $encoded)` / `xpsnr(...)` | `vmaf` / `xpsnr` | two existing files |

`EncodingResult` has `getCRFUsed()` (a float: svt-av1 uses quarter steps like `30.25`), `getVMAFScore()`, `getXPSNRScore()`, `getEstimatedSize()` (bytes), `getEstimatedTime()` (seconds), `getOutputPath()` and `getRawOutput()`.

## Encoder settings

- svt-av1 (`libsvtav1`) is the default. Presets go from 0 (slowest, smallest) to 13 (fastest). CRF goes from 0 to 70; lower is better quality.
- Hardware encoding: `->withEncoder('av1_vaapi')` plus `->withFFmpegOptions(['hwaccel' => 'vaapi', 'hwaccel_output_format' => 'vaapi'])` if needed. Named presets like `medium` only apply to encoders such as `libx264`/`libx265`.
- `withMaxEncodedPercent()` fails the search when the result would be larger than that percentage of the input, which avoids re-encoding sources that are already small.
- Encodes are slow: often longer than the video itself without hardware encoding. Run them in a queued job, and keep the `timeout` config at or below the job's `$timeout`.

## Configuration

Publish with `php artisan vendor:publish --tag=ab-av1-config`. Check the binary and settings with `php artisan ab-av1:info`.

| Key | Purpose |
| --- | --- |
| `binary` | Path to the `ab-av1` binary (default: from `PATH`) |
| `timeout` | Process timeout in seconds |
| `preset`, `min_vmaf`, `max_encoded_percent` | Defaults for every encode |
| `samples`, `vframes` | How many samples ab-av1 tests, and their length |
| `encoder`, `encoder_args`, `pix_format`, `video_filter`, `ffmpeg_input_options` | Encoder and FFmpeg defaults |
| `temporary_files_root` | Downloads, ab-av1's sample files, and encodes before upload |
| `force_generic_input` | Pass a generic input path, so special characters in file names can't break the command |
| `log_channel` | Where commands and output are logged; `false` turns logging off |

## Events

`EncodingStarted` (`$inputPath`, `$options`), `EncodingCompleted` (`$result`, `$executionTime`) and `EncodingFailed` (`$inputPath`, `$exception`, `$executionTime`) are dispatched around every command.

## Errors

All package exceptions extend `RuntimeException`: `MediaNotFoundException` (missing input), `InvalidEncodingConfigurationException` (missing preset or quality target), `ExecutableNotFoundException` (no ab-av1 or FFmpeg) and `EncodingException` (ab-av1 failed; the message includes its error output). Out-of-range values throw `InvalidArgumentException`.

## Testing

Don't run the real binary in tests. Fake it with `Process::fake()`, assert on the recorded command line (it starts with `ab-av1 <subcommand>`), and fake disks with `Storage::fake()`. The package checks for the binaries with `which ab-av1` and `which ffmpeg`, which `Process::fake()` also answers.

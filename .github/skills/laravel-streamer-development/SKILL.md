---
name: laravel-streamer-development
description: Transcode and package video into DASH and HLS with foxws/laravel-streamer (Shaka Streamer), including resolution ladders, codecs, hardware acceleration, AES encryption, exporting to local or S3 disks, and serving manifests with signed URLs through DynamicHLSPlaylist and DynamicDASHManifest. Use when working with the Streamer facade, Foxws\Streamer classes, config/streamer.php, or building adaptive bitrate playlists.
---

# Streaming with laravel-streamer

`foxws/laravel-streamer` wraps [Shaka Streamer](https://shaka-project.github.io/shaka-streamer/), which runs FFmpeg to **transcode** into a resolution ladder and then Shaka Packager to segment it. If the media is already encoded and only needs packaging, `foxws/laravel-shaka` is much faster.

## Streaming flow

```php
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Support\VideoResolution;

$streamer = Streamer::fromDisk('media')->open(['videos/clip.mp4']);

$streamer
    ->useSystemBinaries()
    ->addVideoStream('videos/clip.mp4', 'video.mp4')
    ->addAudioStream('videos/clip.mp4', 'audio.mp4')
    ->withResolutions([VideoResolution::make($height)->last()])
    ->withMpdOutput('index.mpd')
    ->withHlsMasterPlaylist('master.m3u8')
    ->withStreamingMode('vod')
    ->withSegmentPerFile();

try {
    $streamer
        ->export()
        ->toDisk('segments')
        ->toPath("{$playlist->getKey()}/")
        ->afterSaving(fn ($exporter, $result) => $playlist->markAsReady())
        ->save();
} finally {
    $streamer->cleanupTemporaryFiles();
}
```

- The first argument of `add*Stream()` is the path you passed to `open()`. The second is the output filename in the temporary directory.
- `useSystemBinaries()` uses the `ffmpeg` and `packager` on `PATH` instead of the ones bundled with Shaka Streamer. It only affects this streamer instance, so call it for every job that needs it.
- Resolutions: `VideoResolution::make($height)` gives the standard tiers (`144p` … `4k`, `8k`) at or below the source height. Pass the names to `withResolutions()`; don't upscale.
- Always call `cleanupTemporaryFiles()` in `finally`. Transcoding output is large and workers are long-lived.
- `save()` copies the output to the target disk and then deletes the temporary directory. S3 disks upload concurrently, and large files use multipart uploads. Local disks get a `rename()`.
- Captions: use `addTextStream($path, 'caption.mp4', ['language' => 'en'])` and output fragmented MP4 rather than a bare `.vtt`.

## Encryption

```php
$key = $streamer->withAESEncryption('key', 'cbcs');
$streamer->withKeyRotationDuration(600); // optional; needs cenc or cbcs

// Store $key->keyId and $key->key (hex) to serve the key later.
```

Key files are written to `cache_files_root` and uploaded next to the segments. Serve them only through an authorized route or a short-lived signed URL.

## Serving manifests with signed URLs

```php
return Streamer::dynamicHLSPlaylist()
    ->setKeyUrlResolver(fn (string $path) => Storage::disk('segments')->temporaryUrl("{$id}/{$path}", now()->addMinutes(10)))
    ->setMediaUrlResolver(fn (string $path) => Storage::disk('segments')->temporaryUrl("{$id}/{$path}", now()->addHour()))
    ->setPlaylistUrlResolver(fn (string $path) => URL::temporarySignedRoute('manifest', now()->addHour(), [$id, $path]))
    ->fromDisk('segments')
    ->open("{$id}/master.m3u8")
    ->toResponse($request);
```

`Streamer::dynamicDASHManifest()` works the same with `setInitUrlResolver()` and `setMediaUrlResolver()`.

## Configuration

Publish with `php artisan vendor:publish --tag=streamer-config`. Check the install with `php artisan streamer:info`.

| Key | Purpose |
| --- | --- |
| `streamer.streamer_binary` | Path to `shaka-streamer` (`pip install shaka-streamer`) |
| `video_codecs`, `audio_codecs` | Default codecs, e.g. `h264`, `av1`, `aac`, `opus` |
| `hwaccel_api` | Hardware encoding, e.g. `vaapi`, `nvenc` |
| `segment_duration`, `streamer_options`, `extra_input_args` | Pipeline defaults |
| `temporary_files_root` | Where output is written before upload; needs room for every rendition |
| `cache_files_root` | Small files such as keys (default `/dev/shm`) |
| `temporary_files_min_free`, `cache_files_min_free` | Fixed free-space floors; throw `InsufficientStorageException` when a root is too full |
| `concurrency_workers` | Parallel S3 uploads |
| `multipart_threshold`, `multipart_part_size`, `multipart_concurrency` | Multipart upload tuning for large files |
| `timeout` | Process timeout; keep it at or below the queue job's `$timeout` |

## Events

`StreamingStarted`, `StreamingCompleted` (`$result`, `$executionTime`) and `StreamingFailed` are dispatched around each run.

## Testing

Don't run Shaka Streamer in unit tests. Assert on the generated config with `->getCommand()`, and fake the target with `Storage::fake()`.

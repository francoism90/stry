---
name: laravel-shaka-development
description: Package video and audio into DASH and HLS with foxws/laravel-shaka (Shaka Packager), including AES encryption, exporting to local or S3 disks, and serving manifests with signed URLs through DynamicHLSPlaylist and DynamicDASHManifest. Use when working with the Shaka facade, Foxws\Shaka classes, config/laravel-shaka.php, or packaging already-encoded media into streaming playlists.
---

# Packaging with laravel-shaka

`foxws/laravel-shaka` wraps the [Shaka Packager](https://shaka-project.github.io/shaka-packager/html/) binary. Shaka Packager remuxes and segments media that is **already encoded**; it does not transcode. To produce a resolution ladder or change codecs, use `foxws/laravel-streamer` or encode first.

## Packaging flow

```php
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Support\HlsPlaylistType;

$packager = Shaka::fromDisk('media')->open(['videos/clip.mp4']);

$packager
    ->addVideoStream('videos/clip.mp4', 'video.mp4')
    ->addAudioStream('videos/clip.mp4', 'audio.mp4')
    ->withMpdOutput('index.mpd')
    ->withHlsMasterPlaylist('master.m3u8')
    ->withHlsPlaylistType(HlsPlaylistType::Vod);

try {
    $packager
        ->export()
        ->toDisk('segments')
        ->toPath("{$playlist->getKey()}/")
        ->withVisibility('private')
        ->afterSaving(fn ($exporter, $result) => $playlist->markAsReady())
        ->save();
} finally {
    $packager->cleanupTemporaryFiles();
}
```

- The first argument of `add*Stream()` is the path you passed to `open()`; it resolves to a local file. The second is the output filename, written to a temporary directory.
- Always call `cleanupTemporaryFiles()` in `finally`. Jobs run in long-lived workers, and failed jobs would otherwise leave large files behind.
- `save()` copies the output to the target disk and then deletes the temporary directory. S3 disks upload concurrently, and large files use multipart uploads. Local disks get a `rename()`.
- `->dd()` / `->getCommand()` on the exporter shows the packager command without running it.
- Captions: add them with `addTextStream($path, 'caption.mp4', ['language' => 'en', 'dash_roles' => 'subtitle'])`. Output fragmented MP4 rather than `.vtt`: a bare `.vtt` output gets no segment index, and Shaka Player drops it.
- Probe inputs first (for example with FFMpeg) and only add the video or audio streams that exist; the packager fails on a missing stream.

## Encryption

```php
use Foxws\Shaka\Support\ProtectionScheme;

$key = $packager->withAESEncryption('key', ProtectionScheme::Cbcs->value);

// Store $key->keyId and $key->key (hex) to serve the key later.
```

- One key file, named after the first argument, is written to `cache_files_root` and uploaded next to the segments. HLS playlists reference it by that name. Serve it only through an authorized route or a short-lived signed URL.
- `withAESEncryption()` takes the scheme as a string. `null` uses Shaka Packager's default, `cenc`. Use `cbcs` when one set of segments serves both HLS and DASH, including Safari. Avoid `cbc1` and `cens`; few players support them.
- DASH has no key URI. The player needs the key itself, such as Shaka Player's `drm.clearKeys`.
- Key rotation with raw keys is testing-grade in Shaka Packager: it derives later keys from the first, and the package only writes and returns the first key. Don't rely on it without testing full playback.

## Serving manifests with signed URLs

Keep segments private and rewrite manifests per request so every URI is signed:

```php
$handler = Shaka::dynamicHLSPlaylist()
    ->setKeyUrlResolver(fn (string $path) => Storage::disk('segments')->temporaryUrl("{$id}/{$path}", now()->addMinutes(10)))
    ->setMediaUrlResolver(fn (string $path) => Storage::disk('segments')->temporaryUrl("{$id}/{$path}", now()->addHour()))
    ->setPlaylistUrlResolver(fn (string $path) => URL::temporarySignedRoute('manifest', now()->addHour(), [$id, $path]));

return $handler->fromDisk('segments')->open("{$id}/master.m3u8")->toResponse($request);
```

`Shaka::dynamicDASHManifest()` works the same with `setInitUrlResolver()` and `setMediaUrlResolver()`.

## Configuration

Publish with `php artisan vendor:publish --tag=shaka-config`. Check the binary with `php artisan shaka:info`.

| Key | Purpose |
| --- | --- |
| `packager.binaries` | Path to the `packager` binary |
| `segment_duration` | Default segment length in seconds |
| `packager_options` | Array of default options applied to every command |
| `temporary_files_root` | Where segments are written before upload; needs room for the whole output |
| `cache_files_root` | Small files such as keys (default `/dev/shm`) |
| `temporary_files_min_free`, `temporary_files_size_multiplier`, `cache_files_min_free` | Fail fast with `InsufficientStorageException` when a root is too full |
| `concurrency_workers` | Parallel S3 uploads |
| `multipart_threshold`, `multipart_part_size`, `multipart_concurrency` | Multipart upload tuning for large files |
| `timeout` | Process timeout; keep it at or below the queue job's `$timeout` |

## Events

`PackagingStarted`, `PackagingCompleted` (`$result`, `$executionTime`) and `PackagingFailed` (`$exception`, `$executionTime`, `$context`) are dispatched around each run.

## Testing

Don't run the real binary in unit tests. Assert on the command with `->getCommand()`, and fake the target with `Storage::fake()`.

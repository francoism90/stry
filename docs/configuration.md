---
title: Application Configuration
sidebar_position: 4
tags:
    - config
    - environment
    - customizing
---

# Application Configuration

You configure **stry** with environment variables. In development they live in `.env`. In production you store them as the `stry-env` Podman secret with `lpod stry secrets` (see [Podman Quadlet](podman.md)).

:::tip
Use environment variables instead of editing the `config/*.php` files. That keeps your changes in one place and makes deployments easier to repeat.
:::

## Where settings come from

1. **The `stry-env` secret** (production), mounted as `/app/.env` when the container starts.
2. **Your local `.env`** (development), read from the repository folder.
3. **The PHP config files** in `config/*.php`, for fine-grained changes. You rarely need these.

## Admin-managed settings

A few settings are stored in the database instead of `.env`. Admins change them in the app under **Admin → Application / Playback / Chapters / Processing / Reels**, which needs an `admin` or `super-admin` account. They're stored with [spatie/laravel-settings](https://github.com/spatie/laravel-settings), so a change applies to every request right away, without a restart or redeploy.

| Settings class       | Admin tab   | What it covers                                                                      |
| -------------------- | ----------- | ----------------------------------------------------------------------------------- |
| `GeneralSettings`    | Application | Site name, timezone, default locale, registration, profiles per user                |
| `PlaybackSettings`   | Playback    | Subtitle language, stream encryption, URL refresh, rendition heights                |
| `ChapterSettings`    | Chapters    | Patterns and the default type used to classify chapters automatically               |
| `ProcessingSettings` | Processing  | Extracting captions, chapters and storyboards on import, and creating renditions    |
| `ReelSettings`       | Reels       | Creating reels, their size, frame rate, codec, quality, length, cuts and scene sensitivity |

### Shipping new defaults

Changing the default values in a settings class, such as `ChapterSettings::$patterns`, only affects new installs. An existing database already has a stored value for every setting, and it won't pick up changes in the code. To change a value on existing installs, add a new file to `database/settings/` that uses `SettingsMigrator::update()`, instead of editing the old migration:

```php
$blueprint->update('patterns', fn ($patterns): array => array_merge((array) $patterns, [
    'sponsor' => '/\bsponsor(ed|s)?\b/i',
]));
```

Settings migrations run with the normal Laravel migrations, so in production you run `lpod stry artisan migrate --force` as usual (see [Production Setup](production.md#install-and-start-the-services)).

### Cache

```env
SETTINGS_CACHE_ENABLED=true   # cache settings after loading or saving them
SETTINGS_CACHE_MEMO=true      # also keep them in memory for the rest of the request
```

Both are on by default. Saving from the admin pages refreshes the cache automatically. A settings migration writes directly to the database and skips that refresh, so run `settings:clear-cache` after deploying one (see [CLI Interaction](interaction.md#strys-artisan-commands)).

## Essential configuration

Every install needs these settings.

### Application

```env
APP_NAME=stry
APP_ENV=production          # production, local or testing
APP_DEBUG=false             # always false in production
APP_KEY=base64:...          # generate with: php artisan key:generate
APP_URL=https://stry.example.com
APP_TIMEZONE=UTC
APP_LOCALE=en
```

### Database

```env
DB_CONNECTION=pgsql
DB_HOST=systemd-stry-pgsql
DB_PORT=5432
DB_DATABASE=stry
DB_USERNAME=user
DB_PASSWORD=<strong-password>
```

### Cache, sessions and queues

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis      # Horizon only processes Redis queues
```

These use the `stry-valkey` service, a Redis-compatible server.

### Mail

```env
MAIL_MAILER=smtp            # or 'log' to write mail to the log instead
MAIL_FROM_ADDRESS=info@stry.example.com
MAIL_FROM_NAME="Stry"
```

## Advanced configuration

Fine-tune streaming, playback and encoding.

### Direct play

**stry** plays videos directly from their stored file. There are no playlists to generate first: when a player asks for a video, [laravel-media](https://github.com/foxws/laravel-media) builds the DASH manifest and HLS playlist from the file and cuts CMAF (fragmented MP4) segments as they're requested. DASH and HLS share the same segments, so serving both formats doesn't mean packaging twice. A new video can be watched as soon as it's imported.

Along with the video and its audio tracks, each stream carries the video's captions as subtitle tracks (or the subtitles embedded in the file, when it has no captions), seek preview thumbnails, I-frame playlists for trick play and its chapters as markers.

```env
# Length of each segment, in seconds. Shorter segments make seeking faster
# (a seek waits for the segment that contains the target time), but mean
# more HTTP requests.
MEDIA_DELIVERY_SEGMENT_DURATION=6

# Disk and folder where packaged segments are cached
MEDIA_DELIVERY_CACHE_DISK=cache
MEDIA_DELIVERY_CACHE_PATH=media-segments

# Seconds the signed stream URLs stay valid (default: 4 hours)
MEDIA_DELIVERY_URL_LIFETIME=14400

# Number of segments to package ahead of the player
MEDIA_DELIVERY_LOOK_AHEAD=3
```

Segments are cached on the local `/cache` volume, so the app serves them without going through S3. The scheduler runs `media:prune` daily to delete segments older than a week; they're packaged again when they're requested.

Stream URLs are signed and only work for users who can view the video. The player fetches new URLs before they expire, `refresh_before` seconds ahead of time (**Admin → Playback**).

:::note
The segments after the one being watched are packaged ahead of time by jobs on the `media` queue. A Horizon supervisor works that queue, so make sure `stry-horizon` is running, or playback waits for every segment to be packaged on request.
:::

#### Encryption

Turn on **Encryption** under **Admin → Playback** to encrypt the streams: AES-128 for HLS and ClearKey (CENC) for DASH. The segments are encrypted per request with a key derived from `APP_KEY` and the video, so no keys are stored. Changing `APP_KEY` changes every key.

#### Renditions

A stream always offers the original file. To also offer smaller renditions, for slower connections, turn on **Create renditions** under **Admin → Processing** and pick their heights, such as 720 and 480, under **Admin → Playback**. Renditions are encoded while they're watched, at the bitrates of laravel-media's standard ladder.

```env
# Encode renditions on the GPU: none, vaapi, nvenc or qsv
MEDIA_DELIVERY_HARDWARE=vaapi
```

#### Codecs browsers can play

Direct play only works when the browser can decode the file's codecs. These are the codecs that are played as they are:

```env
# Remove hevc if your users watch in Firefox, which only decodes it experimentally
MEDIA_PLAYBACK_VIDEO_CODECS=hevc,h264,av1,vp9
MEDIA_PLAYBACK_AUDIO_CODECS=aac,mp3,opus,flac
```

To play a video in another codec, or to make it smaller, transcode it to AV1 with ab-av1 (see below).

### Videos

```env
# Disk that videos are imported from
VIDEO_IMPORT_DISK=import

# Number of videos to process in each import batch
VIDEO_IMPORT_BATCH_SIZE=20

# How much of a video must be watched before it counts as finished (0.0-1.0)
VIDEO_COMPLETION_THRESHOLD=0.95
```

### Reels

A reel is a short vertical highlight video, shown in the **Reels** feed. To make one, stry finds where the picture changes the most and joins short cuts of those scenes. It skips the first and last 5% of the video and chapters you can skip, such as intros, credits and sponsors. Videos up to one and a half times the reel duration are used whole. The result is cropped to fill the frame and encoded as HEVC (H.265) by default. HEVC is about a third smaller than H.264 but doesn't play in most versions of Firefox, so pick H.264 under **Admin → Reels** if your users watch there. AV1 is also available.

Turn on **Create reels** under **Admin → Reels** to make a reel for each video as it's processed. The same tab sets the size (1080 × 1920 by default), frame rate, quality (a CRF on the CPU, or the matching quality setting on a GPU; 26 by default), length, number of cuts, cut duration and scene sensitivity. Cuts may run past a scene change, and move closer together when they're too far apart to fill most of the reel. Changes only apply to new reels. Run `php artisan videos:reels --force` to regenerate the existing ones, or `videos:reels --missing` to make reels for videos that don't have one yet. Reels are encoded on the `processing` queue.

```env
# Disk that reels are stored on. It must make temporary URLs (S3, or a local
# disk with serve enabled), because the feed plays reels from them.
VIDEO_REELS_DISK=conversions

# Encode reels on the GPU: none, vaapi, nvenc or qsv. Decoding and cropping stay
# on the CPU, and reels fall back to the CPU when the GPU can't be opened.
MEDIA_LADDER_HARDWARE=vaapi
```

### AV1 transcoding (ab-av1)

Transcodes re-encode a video to AV1 at a target quality with [ab-av1](https://github.com/alexheretic/ab-av1). Start one from the **Conversions** tab of a video, or with `transcodes:create`. Once it's imported (from the Transcodes page or with `transcodes:import`), the AV1 file is added to the video's clips, and direct play streams the best clip.

```env
# Encoding preset (0-13 for svt-av1; lower is slower but gives smaller files)
AB_AV1_PRESET=6

# AV1 encoder. Leave it unset to use ab-av1's software default (libsvtav1).
# Options: libsvtav1 (CPU), av1_qsv (Intel QuickSync), av1_vaapi (AMD/Intel VA-API)
AB_AV1_ENCODER=av1_vaapi

# FFmpeg input options for hardware acceleration
# Intel QSV:       "hwaccel=qsv qsv_device=/dev/dri/renderD128"
# AMD/Intel VA-API: "hwaccel=vaapi hwaccel_output_format=vaapi"
AB_AV1_FFMPEG_INPUT_OPTIONS="hwaccel=vaapi hwaccel_output_format=vaapi"

# VMAF quality score to aim for (0-100, default: 94)
AB_AV1_MIN_VMAF=94

# Seconds one encode may run (default: 4 hours)
AB_AV1_TIMEOUT=14400
```

## Config file reference

| Config file         | What it configures     | Main settings                                                           |
| ------------------- | ---------------------- | ----------------------------------------------------------------------- |
| `config/media.php`  | Direct play and FFmpeg | Segments, segment cache, look-ahead, playable codecs, FFmpeg paths      |
| `config/videos.php` | Importing and playback | Import, transcode and thumbnail disks, batch size, completion threshold |
| `config/ab-av1.php` | AV1 encoder            | Preset, encoder, VMAF, timeout, FFmpeg options                          |

The `config/*.php` files are part of the repository, so you can read every option there. Run `php artisan media:info` to check which FFmpeg, FFprobe and ab-av1 binaries were found.

## See also

- [Production Setup](production.md): the security checklist
- [Development Setup](development.md): local configuration
- [S3 Object Storage](s3.md): media storage
- [Laravel configuration basics](https://laravel.com/docs/configuration)
- The video pipeline packages: [laravel-media](https://github.com/foxws/laravel-media) and [laravel-ab-av1](https://github.com/foxws/laravel-ab-av1)

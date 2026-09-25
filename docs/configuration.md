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

A few settings are stored in the database instead of `.env`. Admins change them in the app under **Admin → Application / Playlist / Chapters**, which needs an `admin` or `super-admin` account. They're stored with [spatie/laravel-settings](https://github.com/spatie/laravel-settings), so a change applies to every request right away, without a restart or redeploy.

| Settings class     | Admin tab   | What it covers                                                        |
| ------------------ | ----------- | --------------------------------------------------------------------- |
| `GeneralSettings`  | Application | Site name, timezone, default locale, registration, profiles per user  |
| `PlaylistSettings` | Playlist    | Playlist type, disk, language, encryption, key rotation, cache times  |
| `ChapterSettings`  | Chapters    | Patterns and the default type used to classify chapters automatically |

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

Fine-tune encoding, streaming and playback.

### Playlists

```env
# How playlists are made: 'packager' (fastest, no re-encoding) or 'streamer' (slower, re-encodes)
PLAYLIST_TYPE=packager

# Disk where playlists are stored
PLAYLIST_DISK=segments

# Encryption: 'raw_key_encryption' (AES-128 SAMPLE-AES), 'clearkey' (W3C Clear Key) or null
PLAYLIST_ENCRYPTION=raw_key_encryption

# Protection scheme: 'cenc' (AES-CTR, for Widevine/PlayReady), 'cbcs' (AES-CBC, for FairPlay/Safari), 'cbc1' (legacy) or null (SAMPLE-AES)
PLAYLIST_PROTECTION_SCHEME=cenc

# Rotate the encryption key during playback
PLAYLIST_KEY_ROTATION=false

# Seconds between key rotations
PLAYLIST_KEY_ROTATION_DURATION=300

# Seconds before a playlist expires (default: 14 days)
PLAYLIST_EXPIRES_AFTER=1209600
```

:::note
Both playlist engines package video and audio as CMAF (fragmented MP4) by default. The DASH and HLS manifests then use the same segments, created in a single pass, so serving both formats doesn't mean transcoding twice.
:::

### Videos

```env
# Disk that videos are imported from
VIDEO_IMPORT_DISK=import

# Number of videos to process in each import batch
VIDEO_IMPORT_BATCH_SIZE=20

# Create playlists for imported videos automatically
VIDEO_CREATE_PLAYLIST=false

# How much of a video must be watched before it counts as finished (0.0-1.0)
VIDEO_COMPLETION_THRESHOLD=0.95
```

### Shaka Packager

```env
# Length of each DASH/HLS segment, in seconds. Shorter segments make seeking
# faster (a seek waits for the segment that contains the target time),
# but mean more HTTP requests.
PACKAGER_SEGMENT_DURATION=4

# Number of parallel uploads to S3. Default: 30
PACKAGER_CONCURRENCY_WORKERS=40
```

### Shaka Streamer

```env
# Audio codecs to encode (comma-separated)
STREAMER_AUDIO_CODECS=aac,opus

# Video codecs to encode (comma-separated)
STREAMER_VIDEO_CODECS=hw:h264,hw:vp9

# Length of each segment, in seconds. Same trade-off as
# PACKAGER_SEGMENT_DURATION above.
STREAMER_SEGMENT_DURATION=4

# Number of parallel uploads to S3. Default: 30
STREAMER_CONCURRENCY_WORKERS=40
```

:::note
You don't set Streamer resolutions in `.env`. They're chosen automatically for each video, based on the height of the source video (see `Foxws\Streamer\Support\VideoResolution`).
:::

### AV1 encoding (ab-av1)

```env
# Encoding preset (0-12; higher is slower but gives better quality)
AB_AV1_PRESET=6

# AV1 encoder. Leave it unset to use ab-av1's software default.
# Options: av1_svtenc (CPU), av1_qsv (Intel QuickSync), av1_vaapi (AMD/Intel VA-API)
AB_AV1_ENCODER=av1_vaapi

# FFmpeg input options for hardware acceleration
# Intel QSV:       "hwaccel=qsv qsv_device=/dev/dri/renderD128"
# AMD/Intel VA-API: "hwaccel=vaapi hwaccel_output_format=vaapi"
AB_AV1_FFMPEG_INPUT_OPTIONS="hwaccel=vaapi hwaccel_output_format=vaapi"

# Minimum VMAF quality score (0-100)
AB_AV1_MIN_VMAF=80
```

## Config file reference

| Config file                | What it configures     | Main settings                                                     |
| -------------------------- | ---------------------- | ----------------------------------------------------------------- |
| `config/playlists.php`     | Playlist generation    | Type, encryption, protection, key rotation, expiry                |
| `config/videos.php`        | Importing and playback | Import disk, batch size, creating playlists, completion threshold |
| `config/laravel-shaka.php` | Shaka Packager         | Segment length, parallel uploads, packager arguments              |
| `config/streamer.php`      | Shaka Streamer         | Codecs, resolutions, segment length, parallel uploads             |
| `config/ab-av1.php`        | AV1 encoder            | Preset, encoder, VMAF, FFmpeg options                             |

## Publishing package configuration

To change a package's settings beyond what `.env` offers, publish its config file and edit it:

```bash
# Shaka Packager
php artisan vendor:publish --tag="shaka-config"

# Shaka Streamer
php artisan vendor:publish --tag="streamer-config"

# ab-av1 encoder
php artisan vendor:publish --tag="ab-av1-config"
```

The files are copied to `config/`, where you can edit them.

## See also

- [Production Setup](production.md): the security checklist
- [Development Setup](development.md): local configuration
- [S3 Object Storage](s3.md): media storage
- [Laravel configuration basics](https://laravel.com/docs/configuration)
- The video pipeline packages: [laravel-shaka](https://github.com/foxws/laravel-shaka), [laravel-streamer](https://github.com/foxws/laravel-streamer) and [laravel-ab-av1](https://github.com/foxws/laravel-ab-av1)

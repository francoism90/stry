---
title: Application Configuration
sidebar_position: 4
tags:
    - config
    - environment
    - customizing
---

# Application Configuration

## Overview

**stry** is configured entirely through environment variables — in `.env` (development), or stored as the `stry-env` Podman secret via `lpod stry secrets` (production, see [Podman Quadlet](podman.md)).

:::tip
Prefer environment variables over editing `config/*.php` files directly — it keeps deployments cleaner and configuration portable.
:::

---

## Configuration hierarchy

1. **`stry-env` secret** — mounted as `/app/.env` at container startup (production)
2. **Development `.env`** — local overrides, loaded from the repo (development only)
3. **PHP config files** — fine-grained control via `config/*.php` (rarely needed)

---

## Admin-managed settings

A few settings live in the database instead of `.env` — edited at runtime from **Admin → Application / Playlist / Chapters** in the UI (requires an `admin` or `super-admin` account). They're stored via [spatie/laravel-settings](https://github.com/spatie/laravel-settings), so a save takes effect immediately for every request — no restart or redeploy needed.

| Settings class     | Admin tab   | Covers                                                                        |
| ------------------ | ----------- | ----------------------------------------------------------------------------- |
| `GeneralSettings`  | Application | Site name, timezone, default locale, registration, profiles per user          |
| `PlaylistSettings` | Playlist    | Playlist type, disk, language, encryption, key rotation, cache lifetimes      |
| `ChapterSettings`  | Chapters    | Label-matching regex patterns and default type used to auto-classify chapters |

### Shipping new defaults

Changing a settings class's PHP property defaults (e.g. `ChapterSettings::$patterns`) only affects brand-new installs — an existing database already has a row per property from the original settings migration and won't pick up code changes on its own. To update a value for installs that already ran that migration, add a new file under `database/settings/` using `SettingsMigrator::update()` instead of editing the old one:

```php
$blueprint->update('patterns', fn ($patterns): array => array_merge((array) $patterns, [
    'sponsor' => '/\bsponsor(ed|s)?\b/i',
]));
```

Settings migrations run through Laravel's normal migration runner — `lpod stry artisan migrate --force` on production, same as any schema migration (see [Production Setup](production.md#install-and-start-the-services)).

### Cache

```env
SETTINGS_CACHE_ENABLED=true   # cache settings after load/save
SETTINGS_CACHE_MEMO=true      # also memoize per-request
```

Enabled by default. A normal save from the Admin UI refreshes the cache automatically, but a settings migration writes straight to the database and bypasses that refresh — run `settings:clear-cache` (see [CLI Interaction](interaction.md#strys-artisan-commands)) after deploying one.

---

## Essential configuration

Core settings, required for every deployment.

### Core Application

```env
APP_NAME=stry
APP_ENV=production          # production, local, or testing
APP_DEBUG=false             # false in production
APP_KEY=base64:...          # Generate with: php artisan key:generate
APP_URL=https://stry.example.com
APP_TIMEZONE=UTC
APP_LOCALE=en
```

### Database Connection

```env
DB_CONNECTION=pgsql
DB_HOST=systemd-stry-pgsql
DB_PORT=5432
DB_DATABASE=stry
DB_USERNAME=stry_user
DB_PASSWORD=<strong-password>
```

### Cache & Session

```env
CACHE_STORE=redis
SESSION_DRIVER=database     # or 'cookie'
QUEUE_CONNECTION=database   # or 'sync' for development
```

### Mail

```env
MAIL_MAILER=log            # or 'smtp' for production
MAIL_FROM_ADDRESS=info@stry.example.com
MAIL_FROM_NAME="Stry"
```

---

## Advanced configuration

Fine-tune encoding, streaming, and playback.

### Playlists

```env
# Playlist generation mode: 'packager' (fastest, no re-encoding) or 'streamer' (slower, with encoding)
PLAYLIST_TYPE=packager

# Disk where playlists are stored
PLAYLIST_DISK=segments

# Encryption method: 'raw_key_encryption' (AES-128 SAMPLE-AES), 'clearkey' (W3C Clear Key), or null
PLAYLIST_ENCRYPTION=raw_key_encryption

# Protection scheme: 'cenc' (AES-CTR for Widevine/PlayReady), 'cbcs' (AES-CBC for FairPlay/Safari), 'cbc1' (legacy), null (SAMPLE-AES)
PLAYLIST_PROTECTION_SCHEME=cenc

# Enable encryption key rotation
PLAYLIST_KEY_ROTATION=false

# Duration in seconds before rotating encryption key
PLAYLIST_KEY_ROTATION_DURATION=300

# Playlist expiration time in seconds (default: 14 days)
PLAYLIST_EXPIRES_AFTER=1209600
```

:::note
Both playlist engines package video/audio as CMAF (fragmented MP4) by default, so DASH and HLS manifests are generated from the same set of segments in a single pass — serving both formats doesn't mean double transcoding.
:::

### Videos

```env
# Disk where videos are imported from
VIDEO_IMPORT_DISK=import

# Number of videos to process per batch during import
VIDEO_IMPORT_BATCH_SIZE=20

# Automatically create playlists for imported videos
VIDEO_CREATE_PLAYLIST=false

# Percentage of video watched before marking as complete (0.0-1.0)
VIDEO_COMPLETION_THRESHOLD=0.95
```

### Shaka Packager

```env
# Segment duration for DASH/HLS segments (in seconds). Lower values reduce
# seek latency (a seek waits for the segment covering the target time to
# download) at the cost of more HTTP requests.
PACKAGER_SEGMENT_DURATION=4

# Number of concurrent write workers for s3. Default: 30
PACKAGER_CONCURRENCY_WORKERS=40
```

### Shaka Streamer

```env
# Default audio codecs (comma-separated)
STREAMER_AUDIO_CODECS=aac,opus

# Default video codecs (comma-separated)
STREAMER_VIDEO_CODECS=hw:h264,hw:vp9

# Segment duration for streaming (in seconds). Same seek-latency trade-off
# as PACKAGER_SEGMENT_DURATION above.
STREAMER_SEGMENT_DURATION=4

# Number of concurrent write workers for s3. Default: 30
STREAMER_CONCURRENCY_WORKERS=40
```

:::note
Streamer resolutions aren't set via `.env` — they're detected automatically per video from the source stream's height (see `Foxws\Streamer\Support\VideoResolution`).
:::

### AV1 Encoding (ab-av1)

```env
# Encoding preset (0-12, higher = slower but better quality)
AB_AV1_PRESET=6

# AV1 encoder to use. Leave unset to use ab-av1's software default.
# Options: av1_svtenc (CPU), av1_qsv (Intel QuickSync), av1_vaapi (AMD/Intel VAAPI)
AB_AV1_ENCODER=av1_vaapi

# FFmpeg input options for hardware acceleration
# Intel QSV example: "hwaccel=qsv qsv_device=/dev/dri/renderD128"
# AMD/Intel VAAPI:   "hwaccel=vaapi hwaccel_output_format=vaapi"
AB_AV1_FFMPEG_INPUT_OPTIONS="hwaccel=vaapi hwaccel_output_format=vaapi"

# Minimum VMAF quality score (0-100)
AB_AV1_MIN_VMAF=80
```

---

## Configuration file reference

| Config file                | Description                        | Key settings                                                     |
| -------------------------- | ---------------------------------- | ---------------------------------------------------------------- |
| `config/playlists.php`     | Playlist generation settings       | Type, encryption, protection, key rotation, expiration           |
| `config/videos.php`        | Video import and playback settings | Import disk, batch size, playlist creation, completion threshold |
| `config/laravel-shaka.php` | Shaka Packager options             | Segment duration, concurrency, packager args                     |
| `config/streamer.php`      | Shaka Streamer options             | Codecs, resolutions, segment duration, concurrency               |
| `config/ab-av1.php`        | AV1 encoder options                | Preset, encoder, VMAF, FFmpeg options                            |

---

## Publishing package configuration

For advanced customization, publish the config files and edit them directly:

```bash
# Publish Shaka Packager configuration
php artisan vendor:publish --tag="shaka-config"

# Publish Streamer configuration
php artisan vendor:publish --tag="streamer-config"

# Publish ab-av1 Encoder configuration
php artisan vendor:publish --tag="ab-av1-config"
```

Published files land in `config/` — edit them there.

---

## See also

- [Production Setup](production.md) — security and performance checklist
- [Development Setup](development.md) — local development configuration
- [S3 Object Storage](s3.md) — media storage configuration
- [Laravel Configuration Basics](https://laravel.com/docs/configuration)
- Video pipeline packages — [laravel-shaka](https://github.com/foxws/laravel-shaka), [laravel-streamer](https://github.com/foxws/laravel-streamer), [laravel-ab-av1](https://github.com/foxws/laravel-ab-av1)

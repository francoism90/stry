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

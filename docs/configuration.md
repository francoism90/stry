---
title: Configuration
order: 6
tags:
    - config
    - environment
    - customizing
---

# Configuration

## Introduction

You can customize **stry** by either:

- ✅ **Recommended**: Setting environment variables in `.env` file
- ⚠️ **Alternative**: Modifying configuration files in `config/*.php`

> [!TIP]
> Always prefer environment variables over editing config files directly for easier updates and deployments.

## Environment Variable Examples

These configurations control codec, resolution, timeout, and temporary file handling for each processing engine.

**View full configuration options online:**

- [Laravel Shaka Packager](https://github.com/foxws/laravel-shaka/blob/main/config/laravel-shaka.php) - Segment duration, packager options, encryption
- [Laravel Streamer](https://github.com/foxws/laravel-streamer/blob/main/config/streamer.php) - Audio/video codecs, resolutions, segment duration
- [Laravel ab-av1](https://github.com/foxws/laravel-ab-av1/blob/main/config/ab-av1.php) - Preset, VMAF targeting, encoder options

### Playlists (stry)

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

### Videos (stry)

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
# Segment duration for HLS/DASH segments (in seconds)
PACKAGER_SEGMENT_DURATION=10

# Number of concurrent workers for the HLS packager. Default: 10
PACKAGER_CONCURRENCY_WORKERS=16
```

### Shaka Streamer

```env
# Default audio codecs (comma-separated)
STREAMER_AUDIO_CODECS=aac,opus

# Default video codecs (comma-separated)
STREAMER_VIDEO_CODECS=hw:h264,hw:vp9

# Default resolutions to generate (comma-separated)
STREAMER_RESOLUTIONS=1080p,720p,480p

# Segment duration for streaming (in seconds)
STREAMER_SEGMENT_DURATION=10

# Number of concurrent workers for the video streamer. Default: 10
STREAMER_CONCURRENCY_WORKERS=16
```

### ab-av1 Encoder

```env
# Encoding preset (0-12, higher = slower but better quality)
AB_AV1_PRESET=6

# AV1 encoder to use. Leave unset to use ab-av1's software default.
# Options: av1_svtenc (CPU), av1_qsv (Intel QuickSync), av1_vaapi (AMD/Intel VAAPI)
AB_AV1_ENCODER=av1_vaapi

# FFmpeg input options passed before the input file, typically for hardware acceleration.
# Intel QSV example: "hwaccel=qsv qsv_device=/dev/dri/renderD128"
# AMD/Intel VAAPI:   "hwaccel=vaapi hwaccel_output_format=vaapi"
AB_AV1_FFMPEG_INPUT_OPTIONS="hwaccel=vaapi hwaccel_output_format=vaapi"

# Minimum VMAF quality score (0-100)
AB_AV1_MIN_VMAF=80
```

---

## 🗂️ Configuration Files Overview

The following configuration files are available for customization:

| Config File               | Description                        | Key Features                                                                                                      |
| ------------------------- | ---------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| 📹 `config/playlists.php` | Playlist generation settings       | Type (packager/streamer), encryption (raw_key/clearkey), protection schemes (cenc/cbcs), key rotation, expiration |
| 🎬 `config/videos.php`    | Video import and playback settings | Import disk, batch size, playlist creation, completion threshold, transcode disk                                  |

### Package Configuration

For more advanced configuration of video processing pipelines, you may need to publish and customize the configuration files for the underlying packages:

```bash
# Publish Laravel Shaka Packager configuration
php artisan vendor:publish --tag="shaka-config"

# Publish Laravel Streamer configuration
php artisan vendor:publish --tag="streamer-config"

# Publish Laravel ab-av1 Encoder configuration
php artisan vendor:publish --tag="ab-av1-config"
```

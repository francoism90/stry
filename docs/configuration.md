---
title: Configuration
order: 6
tags:
    - config
    - environment
    - customizing
---

# ⚙️ Configuration

## 📝 Introduction

You can customize **stry** by either:

- ✅ **Recommended**: Setting environment variables in `.env` file
- ⚠️ **Alternative**: Modifying configuration files in `config/*.php`

> [!TIP]
> Always prefer environment variables over editing config files directly for easier updates and deployments.

---

## 🗂️ Configuration Files Overview

The following configuration files are available for customization:

| Config File               | Description              | Key Features |
| ------------------------- | ------------------------ | ------------ |
| 📹 `config/playlists.php` | DASH playlist generation |

    - `disk_name`: Storage disk for playlists (see `config/filesystems.php`).
    - `audio_codecs`: Default audio codecs (e.g. `['opus']`).
    - `video_codecs`: Default video codecs (e.g. `['hw:av1']`).
    - `resolutions`: Array of resolutions to generate (e.g. `['1080p', '720p', '480p']`).
    - `segment_duration`: Segment length in seconds (e.g. `10`).
    - `encryption`: Encryption method (`raw_key_encryption`, `clearkey`, or `null`).
    - `protection_scheme`: DRM protection scheme (`cenc`, `cbcs`, `cbc1`, or `null`).
    - `key_rotation`: Enable key rotation (boolean).
    - `key_rotation_duration`: Key rotation interval in seconds (e.g. `300`).
    - `expires_after`: Playlist expiration in seconds (default: 14 days).
    - `streamer_options`: Extra Shaka Streamer pipeline options (array).
    See file for detailed inline comments and recommended values.

| 🎬 `config/laravel-ffmpeg.php` | FFmpeg video processing | Temporary paths, thread count, encoding parameters. See [Laravel FFmpeg docs](https://github.com/protonemedia/laravel-ffmpeg) |
| 📦 `config/media-library.php` | Media collection management | File handling, conversions. See [Spatie docs](https://spatie.be/docs/laravel-medialibrary/v11/introduction) |

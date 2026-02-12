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

---

## 🗂️ Configuration Files Overview

The following configuration files are available for customization:

| Config File               | Description                                              | Key Features                                                                                                                                             |
| ------------------------- | -------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 📹 `config/playlists.php` | DASH/HLS playlist generation via Shaka Packager/Streamer | Type (packager/streamer), codecs, resolutions, segment duration, encryption (raw_key/clearkey), protection schemes (cenc/cbcs), key rotation, expiration |
| 🎬 `config/videos.php`    | Video import and playback settings                       | Import disk, playlist creation toggle, import batch size, completion threshold                                                                           |

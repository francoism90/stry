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

| Config File                    | Description                 | Key Features                                                                                                |
| ------------------------------ | --------------------------- | ----------------------------------------------------------------------------------------------------------- |
| 📹 `config/playlists.php`       | HLS playlist generation     | Encryption, disks, expire dates
| 🎬 `config/laravel-ffmpeg.php` | FFmpeg video processing     | Temporary paths, thread count, encoding parameters                                                          |
| 📦 `config/media-library.php`  | Media collection management | File handling, conversions. See [Spatie docs](https://spatie.be/docs/laravel-medialibrary/v11/introduction) |

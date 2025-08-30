---
title: Configuration
order: 6
tags:
  - config
  - environment
  - customizing
---

## Introduction

It is possible to overrule the default configuration by setting environment `.env` overrules (recommended) or by adjusting the config files in `config/*.php`.

## Config overview

The following is an overview for config files you may want to adjust:

| Config | Description |
|---|---|
| `config/playlist.php` | This configures playlist (HLS) generating. It allows setting encryption parameters and adding presets for different bitrates. |
| `config/laravel-ffmpeg.php` | This configures ffmpeg parameters such as the temporary path, threads, etc. |

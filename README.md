# 🎬 stry

## Video-on-Demand Platform

[![Tests](https://github.com/francoism90/stry/actions/workflows/tests.yml/badge.svg)](https://github.com/francoism90/stry/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/francoism90/stry)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-3.x-9553E9?logo=inertia)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![Nuxt UI](https://img.shields.io/badge/Nuxt_UI-3.x-00DC82?logo=nuxtdotjs)](https://ui.nuxt.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.x-4169E1?logo=postgresql)](https://www.postgresql.org)
[![FrankenPHP](https://img.shields.io/badge/FrankenPHP-1.x-0A7CFF?logo=php)](https://frankenphp.dev)
[![Podman](https://img.shields.io/badge/Podman-5.x-892CA0?logo=podman)](https://podman.io)

[Demo](https://github.com/francoism90/.github/tree/main/stry) • [Documentation](#-documentation) • [Installation](#-usage)

---

## Introduction

**stry** is a video-on-demand (VOD) media distribution system that allows users to access videos, television shows and movies with streaming capabilities.

### Key Features

- 🎥 **DASH Streaming** - Built-in DASH playlist generation
- 🎚️ **Transcoding** - Generate multiple renditions and bitrates on demand
- 🔐 **Stream Encryption** - Optional secure video content with encryption for both HLS and DASH
- 👤 **Profiles & Content Controls** - Profile-based viewing with optional content hiding
- 📲 **Installable PWA** - Install stry on mobile and desktop
- 🖥️ **Responsive UI** - Modern interface powered by Inertia.js and NuxtUI
- 🚀 **High Performance** - Powered by Laravel Octane and PostgreSQL
- 🔍 **Fast Search** - Lightning-fast search with Typesense
- 🐳 **Container-Ready** - Fully containerized with Podman/Quadlet support

> [!WARNING]
> Always follow [3-2-1](https://www.backblaze.com/blog/the-3-2-1-backup-strategy/) backup plan to protect your media library.

## Demo

For WIP screenshots, please check out: <https://github.com/francoism90/.github/tree/main/stry>

> [!NOTE]
> A hosted demo is planned, but not yet available.

---

## Jellyfin / Plex vs stry

Jellyfin/Plex are media servers first, while **stry** is a streaming delivery platform first.
That means **stry** focuses on repackaging/transcoding and adaptive streaming workflows (DASH-first, HLS-ready), which gives more control but requires a more advanced setup.

| Topic                | Jellyfin / Plex                                   | stry                                                |
| -------------------- | ------------------------------------------------- | --------------------------------------------------- |
| Primary focus        | Personal media server                             | Streaming delivery platform                         |
| Typical setup effort | Faster and simpler                                | More advanced and pipeline-oriented                 |
| Playback model       | Direct library playback plus optional transcoding | Prepared renditions and adaptive streaming delivery |
| Packaging            | Usually less packaging-centric                    | Repackaging/transcoding for streaming-first output  |
| Best fit             | Home library convenience                          | Netflix/YouTube-style streaming workflows           |

---

## Tech Stack

| Category              | Technology                                                                          |
| --------------------- | ----------------------------------------------------------------------------------- |
| **Backend**           | [Laravel 13.x](https://laravel.com/)                                                |
| **Frontend**          | [Inertia 3.x](https://inertiajs.com/) with [NuxtUI](https://ui.nuxt.com/)           |
| **Database**          | [PostgreSQL 18.x](https://www.postgresql.org/)                                      |
| **Containers**        | [Laravel Podman](https://github.com/foxws/laravel-podman) (Podman 5.x)              |
| **Search**            | [Typesense 30.x](https://typesense.org/)                                            |
| **Video Processing**  | [Laravel FFmpeg](https://github.com/protonemedia/laravel-ffmpeg)                    |
| **Video Streaming**   | [Laravel Shaka](https://github.com/foxws/laravel-shaka) (DASH)                      |
| **Video Encoding**    | [Laravel Streamer](https://github.com/foxws/laravel-streamer) (DASH)                |
| **Video Transcoding** | [Laravel ab-av1](https://github.com/foxws/laravel-ab-av1) (beta)                    |
| **PWA**               | [Laravel PWA](https://github.com/foxws/laravel-pwa) (installable on mobile/desktop) |

---

## Prerequisites

You need a basic knowledge of Laravel, Inertia.js, and containerization concepts.
Familiarity with video streaming technologies (DASH, HLS) and encoding (FFmpeg) is a plus.

**System Requirements:**

- Linux (Debian, Fedora, Arch, CentOS, Ubuntu, etc.)
- [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support, or [Docker](https://www.docker.com/) (best-effort)
- Basic tools: `git`, `bash`

> [!NOTE]
> Docker is not officially supported, but a best-effort [Docker Compose setup](docs/docker.md) is available and can be made to work with minor adjustments.

For hardware acceleration: install VAAPI drivers (Intel), mesa packages, or NVENC (Nvidia) dependencies.
See [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html).

---

## Documentation

Comprehensive guides are available in the `docs/` folder:

| Guide                                    | Description                                     |
| ---------------------------------------- | ------------------------------------------------ |
| [Production Setup](docs/production.md)   | Deploy to production                              |
| [Development Guide](docs/development.md) | Local development setup                           |
| [Configuration](docs/configuration.md)   | Configuration options                             |
| [Podman Quadlet](docs/podman.md)         | Container orchestration (services, install, secrets) |
| [Docker Compose](docs/docker.md)         | Alternative, best-effort containerization        |
| [Proxy Setup](docs/proxy.md)             | Reverse proxy configuration                       |
| [S3 Storage](docs/s3.md)                 | Object storage setup                              |
| [Interaction](docs/interaction.md)       | CLI usage and commands                            |

> [!TIP]
> Podman/Quadlet itself is handled by [foxws/laravel-podman](https://github.com/foxws/laravel-podman) — see its own docs for the `lpod` CLI, secrets, and customizing presets. The guides above only cover what's specific to **stry**.

> [!TIP]
> Quick start: Choose between [Production](docs/production.md) or [Development](docs/development.md) setup.

---

## Usage

### Starting the Instance

```bash
systemctl --user start stry proxy
```

The instance will be available at: **<https://stry.test>**

### Seed Database

```bash
vendor/bin/lpod stry a db:seed --force
```

### Creating an Admin User

For testing purposes only, seed a super-admin user:

```bash
vendor/bin/lpod stry a db:seed --class=AdminSeeder
```

> [!WARNING]
> Only seed admins for testing! Never use the seeder in production.

> [!TIP]
> See the [Interaction Guide](docs/interaction.md) for `lpod`, stry's Laravel Sail-style container CLI.

### Admin Services

The following services are only accessible when logged in as **super-admin**:

| Service       | URL                           | Description                     |
| ------------- | ----------------------------- | ------------------------------- |
| **Horizon**   | <https://stry.test/horizon>   | Queue monitoring and management |
| **Telescope** | <https://stry.test/telescope> | Debugging assistant (dev only)  |

---

### License

This project is open-sourced software licensed under the [MIT license](LICENSE).

### AI Statement

This project is developed with AI assistance, primarily using GitHub Copilot and Claude Sonnet.

AI is used for suggestions and acceleration, but all final implementation decisions and adjustments are made by the developers.

AI-assisted pull requests are welcome, as long as an actual person or developer is actively involved in the implementation and review.

### Support

If you find this project useful, please consider giving it a star!

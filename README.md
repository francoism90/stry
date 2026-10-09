# 🎬 stry

## Video-on-Demand Platform

[![Tests](https://github.com/francoism90/stry/actions/workflows/tests.yml/badge.svg)](https://github.com/francoism90/stry/actions/workflows/tests.yml)
[![Build](https://github.com/francoism90/stry/actions/workflows/build.yml/badge.svg)](https://github.com/francoism90/stry/actions/workflows/build.yml)
[![License](https://img.shields.io/github/license/francoism90/stry)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-3.x-9553E9?logo=inertia)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![Nuxt UI](https://img.shields.io/badge/Nuxt_UI-3.x-00DC82?logo=nuxtdotjs)](https://ui.nuxt.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.x-4169E1?logo=postgresql)](https://www.postgresql.org)
[![FrankenPHP](https://img.shields.io/badge/FrankenPHP-1.x-0A7CFF?logo=php)](https://frankenphp.dev)
[![Podman](https://img.shields.io/badge/Podman-5.x-892CA0?logo=podman)](https://podman.io)
[![Demo](https://img.shields.io/badge/Demo-Screenshots-181717)](https://foxws.nl/stry/screenshots)

[Demo](https://foxws.nl/stry/screenshots) • [Documentation](#documentation) • [Installation](#usage)

---

## Introduction

**stry** is a video-on-demand (VOD) platform for streaming videos, TV shows, and movies.

### Key Features

- 🎥 **Direct Play** - Videos stream straight from their files as DASH & HLS, packaged on the fly from shared CMAF segments, no playlists to generate first
- 🎚️ **Renditions & Transcoding** - Optional smaller renditions encoded while they're watched, and AV1 transcodes with ab-av1
- 📱 **Reels** - Short vertical highlight reels cut from each video's best scenes, in a full-screen swipe feed
- 🔐 **Stream Encryption** - Optional secure video content with encryption for both HLS and DASH
- 👤 **Profiles & Content Controls** - Profile-based viewing with optional content hiding
- 📲 **Installable PWA** - Install on mobile and desktop
- 🖥️ **Responsive UI** - Modern interface powered by Inertia.js and NuxtUI
- 🚀 **High Performance** - Powered by Laravel Octane and PostgreSQL
- 🔍 **Fast Search** - Lightning-fast search with Typesense
- 🐳 **Container-Ready** - Fully containerized with Podman/Quadlet support

> [!WARNING]
> Always follow [3-2-1](https://www.backblaze.com/blog/the-3-2-1-backup-strategy/) backup plan to protect your media library.

## Demo

For a visual tour of the app, check out the [screenshots gallery](https://foxws.nl/stry/screenshots).

[![Home page showing the video library grid, search bar, filters, and sidebar navigation](https://raw.githubusercontent.com/francoism90/.github/main/assets/stry/home.webp)](https://foxws.nl/stry/screenshots#home)

> [!NOTE]
> A hosted demo is planned, but not yet available. Screenshots may lag behind active development — expect the UI to evolve as features are added and improved.

---

## Why stry?

**stry** is a streaming delivery platform, not a personal media server like Jellyfin/Plex — see the [full comparison](https://foxws.nl/stry#why-stry) in the docs.

---

## Tech Stack

| Category              | Technology                                                                             |
| --------------------- | -------------------------------------------------------------------------------------- |
| **Backend**           | [Laravel 13.x](https://laravel.com/)                                                   |
| **Frontend**          | [Inertia 3.x](https://inertiajs.com/) with [NuxtUI](https://ui.nuxt.com/)              |
| **Database**          | [PostgreSQL 18.x](https://www.postgresql.org/)                                         |
| **Containers**        | [Laravel Podman](https://github.com/foxws/laravel-podman) (Podman 5.x)                 |
| **Search**            | [Typesense 30.x](https://typesense.org/)                                               |
| **Video Streaming**   | [Laravel Media](https://github.com/foxws/laravel-media) (direct-play HLS + DASH, CMAF) |
| **Video Transcoding** | [Laravel ab-av1](https://github.com/foxws/laravel-ab-av1) (beta)                       |
| **PWA**               | [Laravel PWA](https://github.com/foxws/laravel-pwa) (installable on mobile/desktop)    |

---

## Prerequisites

Requires Linux with [Podman 5.3+](https://podman.io/)/Quadlet, or [Docker](https://www.docker.com/) (best-effort). Basic knowledge of Laravel, Inertia.js, and containers helps. See [Podman Quadlet](https://foxws.nl/stry/podman#prerequisites) or [Docker Compose](https://foxws.nl/stry/docker#prerequisites) for the full system requirements.

---

## Documentation

Comprehensive guides are available on the [documentation site](https://foxws.nl/stry) (or browse [`docs/`](docs) directly):

| Guide                                                  | Description                                          |
| ------------------------------------------------------ | ---------------------------------------------------- |
| [Screenshots](https://foxws.nl/stry/screenshots)       | Visual tour of the app                               |
| [Production Setup](https://foxws.nl/stry/production)   | Deploy to production                                 |
| [Upgrading](https://foxws.nl/stry/upgrading)           | Update an existing production install                |
| [Development Guide](https://foxws.nl/stry/development) | Local development setup                              |
| [Configuration](https://foxws.nl/stry/configuration)   | Configuration options                                |
| [Podman Quadlet](https://foxws.nl/stry/podman)         | Container orchestration (services, install, secrets) |
| [Docker Compose](https://foxws.nl/stry/docker)         | Alternative, best-effort containerization            |
| [Reverse Proxy](https://foxws.nl/stry/proxy)           | Sibling-service routing; bring your own HTTPS        |
| [S3 Storage](https://foxws.nl/stry/s3)                 | Object storage setup                                 |
| [Interaction](https://foxws.nl/stry/interaction)       | CLI usage and commands                               |

> [!TIP]
> Quick start: pick [Production](https://foxws.nl/stry/production) or [Development](https://foxws.nl/stry/development) setup. Podman/Quadlet itself is handled by [foxws/laravel-podman](https://github.com/foxws/laravel-podman), paired with the standalone [`lpod`](https://github.com/foxws/lpod) CLI — see their own docs for secrets and customizing presets. The guides above only cover what's specific to **stry**.

---

## Usage

```bash
systemctl --user start stry
```

The instance will be available at: **<http://localhost:8000>** (or your own domain, once you've set up a [reverse proxy](https://foxws.nl/stry/proxy) in front of it)

See the [Interaction Guide](https://foxws.nl/stry/interaction) for `lpod`, stry's Laravel Sail-style container CLI, and [Production Setup](https://foxws.nl/stry/production#install-and-start-the-services)/[Development Setup](https://foxws.nl/stry/development#admin-account) for seeding and creating admin users.

---

### License

This project is open-sourced software licensed under the [MIT license](LICENSE).

### AI Statement

This project is developed with AI assistance, primarily using GitHub Copilot and Claude.

AI is used for suggestions and acceleration, but all final implementation decisions and adjustments are made by the developers.

AI-assisted pull requests are welcome, as long as an actual person or developer is actively involved in the implementation and review.

### Support

If you find this project useful, please consider giving it a star!

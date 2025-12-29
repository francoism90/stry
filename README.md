# 🎬 stry

### Video-on-Demand Platform

[![Tests](https://github.com/francoism90/stry/actions/workflows/tests.yml/badge.svg)](https://github.com/francoism90/stry/actions/workflows/tests.yml)
[![License](https://img.shields.io/github/license/francoism90/stry)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-2.x-9553E9?logo=inertia)](https://inertiajs.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.x-4169E1?logo=postgresql)](https://www.postgresql.org)
[![Podman](https://img.shields.io/badge/Podman-5.x-892CA0?logo=podman)](https://podman.io)
[![FrankenPHP](https://img.shields.io/badge/FrankenPHP-1.x-0A7CFF?logo=php)](https://frankenphp.dev)

[Demo](https://github.com/francoism90/.github/tree/main/stry) • [Documentation](#-documentation) • [Installation](#-usage)

---

## 🎯 Introduction

**stry** is a video-on-demand (VOD) media distribution system that allows users to access videos, television shows and movies with streaming capabilities.

### ✨ Key Features

- 🎥 **HLS Streaming** - Built-in HLS playlist generation with adaptive bitrate support
- 🔐 **HLS Encryption** - Secure video content with encryption
- 📱 **Responsive UI** - Modern interface powered by Inertia.js and NuxtUI
- 🚀 **High Performance** - Powered by Laravel Octane and PostgreSQL
- 🔍 **Fast Search** - Lightning-fast search with Typesense
- 🐳 **Container-Ready** - Fully containerized with Podman/Quadlet support

> [!NOTE]
> This is a personal project that can be used personally or as a reference guide for building your own streaming platform.

---

## 📸 Demo

For WIP screenshots, please check out: <https://github.com/francoism90/.github/tree/main/stry>

> [!NOTE]
> A hosted demo is planned, but not yet available.

---

## 🛠️ Tech Stack

| Category             | Technology                                                                |
| -------------------- | ------------------------------------------------------------------------- |
| **Backend**          | [Laravel 12.x](https://laravel.com/)                                      |
| **Frontend**         | [Inertia 2.x](https://inertiajs.com/) with [NuxtUI](https://ui.nuxt.com/) |
| **Database**         | [PostgreSQL 18.x](https://www.postgresql.org/)                            |
| **Containers**       | [Podman 5.x](https://podman.io/)                                          |
| **Search**           | [Typesense 29.x](https://typesense.org/)                                  |
| **Video Processing** | [Laravel FFmpeg](https://github.com/protonemedia/laravel-ffmpeg)          |
| **Video Streaming**  | [Laravel Shaka](https://github.com/foxws/laravel-shaka)                   |

---

## 📋 Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, Arch, CentOS, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support
- 🛠️ Basic tools: `git`, `bash`

---

## 📚 Documentation

Comprehensive guides are available in the `docs/` folder:

| Guide                                       | Description                 |
| ------------------------------------------- | --------------------------- |
| [🚀 Production Setup](docs/production.md)   | Deploy to production        |
| [💻 Development Guide](docs/development.md) | Local development setup     |
| [⚙️ Configuration](docs/configuration.md)   | Configuration options       |
| [🔧 System Setup](docs/system.md)           | System requirements         |
| [🐳 Podman Guide](docs/podman.md)           | Container management        |
| [🌐 Proxy Setup](docs/proxy.md)             | Reverse proxy configuration |
| [☁️ S3 Storage](docs/s3.md)                 | Object storage setup        |
| [🎮 Interaction](docs/interaction.md)       | CLI usage and commands      |

> [!TIP]
> Quick start: Choose between [Production](docs/production.md) or [Development](docs/development.md) setup.

---

## 🚀 Usage

### Starting the Instance

```bash
systemctl --user start stry proxy
```

The instance will be available at: **<https://stry.test>**

### Creating an Admin User

For testing purposes only, seed a super-admin user:

```bash
stry a db:seed --class=AdminSeeder
```

> [!WARNING]
> Only seed admins for testing! Never use the seeder in production.

> [!TIP]
> See the [Interaction Guide](docs/interaction.md) for a Laravel Sail-style shell utility approach.

### Admin Services

The following services are only accessible when logged in as **super-admin**:

| Service          | URL                           | Description                     |
| ---------------- | ----------------------------- | ------------------------------- |
| 🎛️ **Admin**     | <https://stry.test/admin>     | Admin dashboard (WIP)           |
| 🌊 **Horizon**   | <https://stry.test/horizon>   | Queue monitoring and management |
| 🔭 **Telescope** | <https://stry.test/telescope> | Debugging assistant (dev only)  |

---

### 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

### ⭐ Support

If you find this project useful, please consider giving it a star!

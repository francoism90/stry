# Stry

## Introduction

Stry is a video on demand (VOD) media distribution system that allows users to access to videos, television shows and movies.

It uses the [laravel-ffmpeg](https://github.com/protonemedia/laravel-ffmpeg#hls) package, offering built-in HLS playlist generation with segment encryption and authorization.

> **NOTE:** Stry is still in development, do not use on production.

## Details

Stry uses the following stack:

- [Laravel 12.x](https://laravel.com/)
- [Inertia 2.x](https://inertiajs.com/) with [NuxtUI](https://ui.nuxt.com/)
- [PostgreSQL 17.x](https://www.postgresql.org/)
- [Podman 5.x](https://podman.io/)
- [Meilisearch 1.x](https://www.meilisearch.com/)

## Prerequisites

- Linux (Debian, Fedora, Arch, CentOS, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- `git`, `bash`, etc.

## Installation

1. Clone the repository, for example to `~/projects`:

```bash
cd ~/projects
git https://github.com/francoism90/stry.git
```

1. Setup a local data path:

```bash
mkdir -p /home/user/data/stry/{media,import}
```

1. Setup for [production](docs/production.md) or [development](docs/development.md).

## Usage

To run the instance after following the installation:

```bash
systemctl --user start stry proxy
```

The instance should be available at <https://stry.test>.

To seed an example super-admin user (only do this for testing!):

```bash
php artisan db:seed --class=AdminSeeder
```

The following services are only accessible when being logged in as *super-admin*:

- <https://stry.test/horizon> - Laravel Horizon
- <https://stry.test/telescope> - Laravel Telescope (only available on development)

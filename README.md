# Stry

## Introduction

Stry is a video on demand (VOD) media distribution system that allows users to access to videos, television shows and movies.

By using the [laravel-ffmpeg](https://github.com/protonemedia/laravel-ffmpeg#hls) package, it offers built-in HLS playlist generation with segment encryption and authorization.

> Please note Stry is still in development.

## Details

Stry uses the following stack:

- [Laravel 12.x](https://laravel.com/)
- [Inertia 2.x](https://inertiajs.com/)
- [PostgreSQL 17.x](https://www.postgresql.org/)
- [Podman 5.x](https://podman.io/)
- [Meilisearch 1.x](https://www.meilisearch.com/)

## Prerequisites

- Linux (Debian, Fedora, Arch, CentOS, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

## Installation

### Clone repository

1. Clone the repository, for example to `~/projects`:

```bash
cd ~/projects
git https://gitstry.com/francoism90/stry.git
```

1. Setup [Podman Quadlet](docs/podman.md).

1. Open the project with VSCode and run it as a dev-container.

1. Perform the following commands in a terminal:

```bash
composer install
php artisan storage:link
php artisan key:generate
php artisan google-fonts:fetch
php artisan migrate --seed
pnpm install && pnpm build
```

1. To seed example users:

```bash
php artisan db:seed --class=UserSeeder
```

## Usage

The instance should be available at <https://stry.test>.

The following services are only accessible when being a *super-admin*:

- <https://stry.test/horizon> - Laravel Horizon
- <https://stry.test/telescope> - Laravel Telescope (disabled by default - only use on development)

## Upgrading

See [UPGRADING.md](UPGRADING.md)

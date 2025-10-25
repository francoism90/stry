# stry

## Introduction

stry is a video-on-demand (VOD) media distribution system that allows users to access to videos, television shows and movies.

It uses the [laravel-ffmpeg](https://github.com/protonemedia/laravel-ffmpeg#hls) package, offering built-in HLS playlist generation with bitrate support, segment encryption and authorization.

This is a personal project, that can be either use personally or as a reference guide for building your own streaming platform.

## Demo

For WIP screenshots, please checkout: <https://github.com/francoism90/.github/tree/main/stry>

A hosted demo is planned, but not yet available.

## Details

It's build around the following stack:

- [Laravel 12.x](https://laravel.com/)
- [Inertia 2.x](https://inertiajs.com/) with [NuxtUI](https://ui.nuxt.com/)
- [PostgreSQL 18.x](https://www.postgresql.org/)
- [Podman 5.x](https://podman.io/)
- [Typesense 29.x](https://typesense.org/)

## Prerequisites

- Linux (Debian, Fedora, Arch, CentOS, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- `git`, `bash`, etc.

## Documentation

A documentation site is still in the works, however most things are documented as markdown inside `docs` folder of this repo.

Quick start by choosing between [production](docs/production.md) or [development](docs/development.md).

## Usage

To run the instance after install:

```bash
systemctl --user start stry proxy
```

The instance should be available at <https://stry.test>.

To seed an example super-admin user (only do this for testing!):

```bash
stry a db:seed --class=AdminSeeder
```

> **TIP**: See [interaction](docs/interaction.md) for using a Laravel Sail shell utility approach.

The following services are only accessible when being logged in as *super-admin*:

- <https://stry.test/horizon> - Laravel Horizon
- <https://stry.test/telescope> - Laravel Telescope (only available on development)

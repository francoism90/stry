---
title: Documentation
slug: /
sidebar_position: 1
tags:
    - guides
    - documentation
    - index
metadata:
    kind: personal
    type: App
    status: active
    eyebrow: 'Streaming · Laravel · Inertia'
    desc: A streaming platform built with Laravel and Inertia.js.
---

# Documentation

## Why stry?

Jellyfin and Plex are media servers first. **stry** is built for streaming first: it plays your videos directly from their files as DASH and HLS streams, packaged on the fly, with optional encryption, smaller renditions and AV1 transcoding. You get more control over how video is delivered, but setup takes more work.

| Topic         | Jellyfin / Plex                                    | stry                                              |
| ------------- | -------------------------------------------------- | ------------------------------------------------- |
| Primary focus | Personal media server                              | Streaming platform                                |
| Setup effort  | Quick and simple                                   | More involved                                     |
| Playback      | Plays library files directly, transcodes if needed | Plays files directly as DASH and HLS streams      |
| Packaging     | Mostly plays files as they are                     | Packages segments on the fly, nothing up front    |
| Best for      | A convenient home library                          | Netflix- or YouTube-style streaming               |

## Getting started

- **[Production Setup](production.md)**: start here to deploy stry on a server.
- **[Development Setup](development.md)**: start here to work on stry locally.
- **[Screenshots](screenshots.md)**: see what stry looks like before you install it.

## Guides

| Guide                                         | What it covers                                |
| --------------------------------------------- | --------------------------------------------- |
| [Podman Quadlet](podman.md)                   | Running the services: install, secrets, GPU   |
| [Docker Compose](docker.md)                   | An alternative setup, maintained best-effort  |
| [Reverse Proxy](proxy.md)                     | Subdomain routing and bringing your own HTTPS |
| [Object Storage (S3)](s3.md)                  | S3-compatible storage for generated images    |
| [Application Configuration](configuration.md) | Direct play, video and encoding settings      |
| [CLI Interaction](interaction.md)             | `lpod` and stry's own Artisan commands        |
| [Upgrading](upgrading.md)                     | Updating an existing production install       |

:::tip
The Podman side is handled by [foxws/laravel-podman](https://github.com/foxws/laravel-podman) and its standalone [`lpod`](https://github.com/foxws/lpod) CLI. Their docs cover the general topics, such as secrets and customizing presets. These guides only cover what is specific to **stry**.
:::

## Key concepts

- **`stry-env` secret**: the app's `.env` file, stored as a Podman secret and mounted at `/app/.env` in every app container.
- **Presets**: the templates in `containers/stubs/{preset}/`. `production` runs the app and its services, `development` runs them against your local code, `s3` sets up buckets and CORS, and `devcontainer` builds a VS Code development container.
- **Quadlet**: the systemd integration that starts the containers in the right order on boot.

## Common tasks

```bash
# Start, stop and check status
lpod stry up
lpod stry down
systemctl --user status stry

# Follow the logs
journalctl --user -u stry -f

# Open a shell or run Artisan
lpod stry shell
lpod stry artisan migrate --force
```

## Need help?

- Read [Podman Quadlet](podman.md) and the [`lpod` reference](https://github.com/foxws/lpod).
- Check the logs with `journalctl --user -u stry -f` or `podman logs -f systemd-stry`.

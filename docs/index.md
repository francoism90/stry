---
title: Documentation
order: 0
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
    image: https://raw.githubusercontent.com/francoism90/.github/main/assets/stry/home-hero.webp
---

# Documentation

## Why stry?

Jellyfin and Plex are media servers first. **stry** is built for streaming first: it plays your videos directly from their files as DASH and HLS streams, packaged on the fly, with optional encryption, smaller renditions and AV1 transcoding. You get more control over how video is delivered, but setup takes more work.

| Topic         | Jellyfin / Plex                                    | stry                                           |
| ------------- | -------------------------------------------------- | ---------------------------------------------- |
| Primary focus | Personal media server                              | Streaming platform                             |
| Setup effort  | Quick and simple                                   | More involved                                  |
| Playback      | Plays library files directly, transcodes if needed | Plays files directly as DASH and HLS streams   |
| Packaging     | Mostly plays files as they are                     | Packages segments on the fly, nothing up front |
| Best for      | A convenient home library                          | Netflix- or YouTube-style streaming            |

## Getting started

- **[Production Setup](production.md)**: start here to set stry up on a server, with [Pods](https://github.com/foxws/pods) or by hand.
- **[Development Setup](development.md)**: start here to work on stry locally.
- **[Screenshots](screenshots.md)**: see what stry looks like before you install it.

## Guides

| Guide                                         | What it covers                                |
| --------------------------------------------- | --------------------------------------------- |
| [Podman Quadlet](podman.md)                   | Running the services: install, secrets, GPU   |
| [Docker Compose](docker.md)                   | An alternative setup, maintained best-effort  |
| [Reverse Proxy](proxy.md)                     | Subdomain routing and bringing your own HTTPS |
| [Object Storage (S3)](s3.md)                  | S3-compatible storage for generated images    |
| [Database Snapshots](backups.md)              | Scheduled database backups and restoring them |
| [Application Configuration](configuration.md) | Direct play, video and encoding settings      |
| [CLI Interaction](interaction.md)             | `lpod` and stry's own Artisan commands        |
| [Upgrading](upgrading.md)                     | Updating an existing production install       |

:::tip
**stry** ships plain Podman Quadlet units, which you can install with [Pods](https://github.com/foxws/pods), with `podman quadlet install`, or by hand. The optional [`lpod`](https://github.com/foxws/lpod) CLI runs Artisan and other commands in the containers. See the [Podman Quadlet documentation](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html) for the general topics.
:::

## Key concepts

- **`stry-env` secret**: the app's `.env` file, stored as a Podman secret and mounted at `/app/.env` in every app container.
- **Units**: the Quadlet files in `containers/quadlets/{production,development}/`. `production` runs the app and its services from the published image, `development` runs them against your local code. `pods.yml` holds the settings of the optional on-demand services, and `containers/devcontainer/` a VS Code development container.
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

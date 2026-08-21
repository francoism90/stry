---
title: Documentation
slug: /
sidebar_position: 1
tags:
    - guides
    - documentation
    - index
---

# Documentation

## Getting started

**[Production Deployment](production.md)** — deploying to a server? Start here.

**[Development Setup](development.md)** — developing locally? Start here.

## Guides

| Guide                                         | Description                                  |
| --------------------------------------------- | -------------------------------------------- |
| [Podman Quadlet](podman.md)                   | Running the services (install, secrets, GPU) |
| [Docker Compose](docker.md)                   | Alternative setup, best-effort               |
| [Reverse Proxy](proxy.md)                     | Sibling routing; bring your own HTTPS        |
| [Object Storage (S3)](s3.md)                  | S3-compatible storage for media and segments |
| [Application Configuration](configuration.md) | Playlist, video, and encoding settings       |
| [CLI Interaction](interaction.md)             | `lpod` and stry's own Artisan commands       |

> [!TIP]
> Podman/Quadlet itself is handled by [foxws/laravel-podman](https://github.com/foxws/laravel-podman), paired with the standalone [`lpod`](https://github.com/foxws/lpod) CLI — see their docs for anything generic (secrets, customizing presets). The guides above only cover what's specific to **stry**.

## Key concepts

- **`stry-env` secret** — the app's `.env` file, stored as a Podman secret and mounted at `/app/.env` in every app-role container
- **Presets** — `containers/stubs/{preset}/`: `frankenphp-octane` (app + services), `s3` (buckets/CORS), `devcontainer` (VS Code)
- **Quadlet** — systemd integration that runs containers, in the right order, on boot

## Common tasks

```bash
# Start/stop/status
lpod stry up
lpod stry down
systemctl --user status stry

# Logs
journalctl --user -u stry -f

# Shell / Artisan
lpod stry shell
lpod stry artisan migrate --force
```

## Need help?

- [Podman Quadlet](podman.md) and the [`lpod` reference](https://github.com/foxws/lpod)
- `journalctl --user -u stry -f` / `podman logs -f systemd-stry`

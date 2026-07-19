---
title: Documentation
tags:
    - guides
    - documentation
    - index
---

# Documentation

Welcome to **stry** documentation. Choose your path below:

## 🚀 Getting Started

**[Production Deployment](production.md)** — Ready to deploy? Start here.

**[Development Setup](development.md)** — Want to develop locally? Start here.

## 📚 Guides

| Guide                                    | Description                                        |
| ----------------------------------------- | --------------------------------------------------- |
| [Podman Quadlet](podman.md)               | Container orchestration (services, install, secrets)|
| [Docker Compose](docker.md)               | Alternative, best-effort containerization           |
| [Proxy Configuration](proxy.md)           | Caddy reverse proxy setup with automatic HTTPS      |
| [Object Storage (S3)](s3.md)              | S3-compatible storage for media and segments        |
| [Application Configuration](configuration.md) | Playlist, video, and encoding settings          |
| [CLI Interaction](interaction.md)         | `lpod` and stry's own Artisan commands              |

> [!TIP]
> Podman/Quadlet is handled by [foxws/laravel-podman](https://github.com/foxws/laravel-podman), paired with the standalone [`lpod`](https://github.com/foxws/lpod) CLI — their own docs are the reference for secrets and customizing presets. The guides above only cover what's specific to **stry**.

## Key Concepts

- **`stry-env` secret** — the application's `.env` file, stored as a Podman secret and mounted at `/app/.env` in every app-role container
- **Presets** — `containers/stubs/{preset}/` — `frankenphp-octane` (app + services), `proxy` (Caddy), `s3` (buckets/CORS), `devcontainer` (VS Code)
- **Quadlet** — systemd integration managing containers with automatic dependency ordering

## Common Tasks

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

## Need Help?

- 📖 [Podman Quadlet](podman.md) and the [`lpod` reference](https://github.com/foxws/lpod)
- 🐛 `journalctl --user -u stry -f` / `podman logs -f systemd-stry`

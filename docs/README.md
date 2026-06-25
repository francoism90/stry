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

- Complete Podman/Quadlet setup
- Security and performance guidance
- Production best practices

**[Development Setup](development.md)** — Want to develop locally? Start here.

- Development environment configuration
- VSCode devcontainer setup
- Local testing and debugging

---

## 📚 Detailed Guides

### Container Orchestration

- **[Podman Quadlet](podman.md)** — Systemd integration overview
    - [Quick Start](podman-quickstart.md) — 5-minute setup
    - [Configuration](podman-configuration.md) — Detailed environment setup
    - [Operations](podman-operations.md) — Daily container management
    - [Reference](podman-reference.md) — Resource limits and technical details

- **[Docker Compose](docker.md)** — Alternative containerization approach (Linux/Mac/Windows)

### Infrastructure

- **[Proxy Configuration](proxy.md)** — Caddy reverse proxy setup with automatic HTTPS
- **[Object Storage (S3)](s3.md)** — S3-compatible storage for media and segments
- **[System Configuration](system.md)** — Resource limits and performance tuning

### Application

- **[Application Configuration](configuration.md)** — Custom app settings (playlists, videos, codecs)
- **[CLI Interaction](interaction.md)** — Shell utilities and Artisan commands

---

## Quick Navigation

| Need to...               | See...                                        |
| ------------------------ | --------------------------------------------- |
| Deploy to production     | [Production Setup](production.md)             |
| Start developing locally | [Development Setup](development.md)           |
| Set up containers        | [Podman Quick Start](podman-quickstart.md)    |
| Configure app settings   | [Application Configuration](configuration.md) |
| Set up HTTPS with Caddy  | [Proxy Configuration](proxy.md)               |
| Configure S3 storage     | [Object Storage](s3.md)                       |
| Manage containers daily  | [Podman Operations](podman-operations.md)     |
| Tune performance         | [System Configuration](system.md)             |
| Use CLI commands         | [CLI Interaction](interaction.md)             |

---

## Document Organization

All guides follow this structure:

- **Introduction** — What the document covers
- **Prerequisites** — What you need before starting
- **Setup/Configuration** — Step-by-step instructions
- **Usage** — How to use the feature
- **Troubleshooting** — Common issues and solutions
- **Reference** — Technical details (tables, commands, etc.)

---

## Key Concepts

### Runtime Model

- **global.env** — Single source of truth for application configuration at runtime, mounted from host at `/config/global.env`
- **APP_SERVICE** — Determines which service the container runs (app, ssr, horizon, reverb, scheduler)
- **APP_RUNTIME_ENV** — Controls production-specific startup behavior (production, local, testing)
- **Quadlet** — Systemd integration for managing containers with automatic dependency ordering

### Development vs Production

- **Development** — Mount application code in container for live editing, set `APP_RUNTIME_ENV=local`
- **Production** — Application code baked into image, set `APP_RUNTIME_ENV=production`, use runtime `global.env` for configuration

---

## Common Tasks

### Start Services

```bash
systemctl --user start stry
journalctl --user -u stry -f  # View logs
```

### Run Database Migrations

```bash
podman exec systemd-stry php artisan migrate --force
```

### Access CLI

```bash
alias stry='~/projects/stry/bin/quadlet'
stry shell           # Enter container shell
stry artisan help    # Run Artisan commands
```

### Check Container Status

```bash
systemctl --user status stry
podman stats         # Real-time resource usage
```

---

## Need Help?

- 💬 Check the [troubleshooting section](podman-operations.md#troubleshooting) in relevant guides
- 📖 Review [Podman documentation](https://docs.podman.io/)
- 🐛 Inspect logs with `journalctl --user -u stry -f`
- 🔍 Check container logs with `podman logs -f systemd-stry`

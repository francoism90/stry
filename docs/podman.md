---
title: Podman Quadlet
order: 3
tags:
    - podman
    - quadlet
    - containers
    - systemd
---

# Podman Quadlet

**stry** ships as [Podman Quadlet](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html) units, rendered and installed with [foxws/laravel-podman](https://github.com/foxws/laravel-podman). That package's own docs are the reference for anything generic — the `lpod` CLI, secrets, customizing presets, setting up without PHP on the host, etc. This page only covers what's specific to **stry**.

## Prerequisites

- Linux with systemd (rootless or system-wide)
- [Podman 5.3+](https://podman.io/) with the `quadlet` CLI plugin (`podman quadlet --help` should work)

## Presets

Published under `containers/stubs/` — customize a preset there (see [Customizing](https://github.com/foxws/laravel-podman/blob/main/docs/customizing.md)) without touching the others:

| Preset              | Purpose                                                          |
| ------------------- | ---------------------------------------------------------------- |
| `frankenphp-octane` | The application and its sibling services (table below)           |
| `proxy`             | Caddy reverse proxy — see [Proxy](proxy.md)                      |
| `s3`                | RustFS bucket/CORS setup — see [S3](s3.md)                       |
| `devcontainer`      | VS Code Dev Containers image — see [Development](development.md) |

`frankenphp-octane` installs these services (unit names use the `stry` prefix by default — the `PODMAN_QUADLET_PREFIX` env var, which defaults to `APP_NAME`):

| Unit               | Role                                           |
| ------------------ | ---------------------------------------------- |
| `stry`             | HTTP app server (Octane) — :8000, :5173 (Vite) |
| `stry-pgsql`       | PostgreSQL — :5432                             |
| `stry-valkey`      | Cache/queue backend — :6379                    |
| `stry-horizon`     | Queue worker                                   |
| `stry-reverb`      | WebSocket server — :6001                       |
| `stry-schedule`    | Scheduler                                      |
| `stry-inertia-ssr` | Inertia SSR — :13714                           |
| `stry-mailpit`     | Dev mail catcher — :8025                       |
| `stry-rustfs`      | S3-compatible storage — :9000 / :9001          |
| `stry-typesense`   | Search — :8108                                 |

## Install

```bash
php artisan podman:setup   # renders every preset above into podman/{preset}/

# Install every rendered service (see the "podman/{preset}/" folder for the full list):
vendor/bin/lpod install frankenphp-octane/app.quadlets --replace
vendor/bin/lpod install frankenphp-octane/pgsql.quadlets --replace
# ...

# Then set each service's secrets:
vendor/bin/lpod secrets stry
vendor/bin/lpod secrets stry-pgsql
# ...

vendor/bin/lpod stry up
```

See the package's [Quick Start](https://github.com/foxws/laravel-podman#quick-start) for the full flow, and [Setting up without PHP on the host](https://github.com/foxws/laravel-podman/blob/main/docs/host-setup.md) if Podman and PHP aren't on the same machine.

## Day-to-day

```bash
vendor/bin/lpod stry up                    # start
vendor/bin/lpod stry shell                 # shell in
vendor/bin/lpod stry artisan migrate       # run Artisan
systemctl --user status stry               # or use systemctl/journalctl directly
journalctl --user -u stry -f
```

See [CLI Interaction](interaction.md) for stry's own Artisan commands, and the package's [`lpod` reference](https://github.com/foxws/laravel-podman/blob/main/docs/lpod.md) for the full command set (`secrets`, `remove`, `list`, `print`, `uninstall`, ...).

## Tuning & hardware acceleration

Resource limits (`Memory=`, `ShmSize=`, ...) live directly in `containers/stubs/frankenphp-octane/quadlets/*.quadlets`. After editing, re-render (`php artisan podman:generate frankenphp-octane`) and reinstall (`lpod install ... --replace`).

The application image bundles VA-API drivers, and `horizon.quadlets` passes `/dev/dri` through to `stry-horizon` by default for hardware-accelerated transcoding:

```ini
[Container]
AddDevice=/dev/dri:/dev/dri
GroupAdd=keep-groups
```

> [!NOTE]
> `/dev/dri` must exist on the host — on a machine without a GPU (or most cloud/CI boxes), `stry-horizon` will fail to start unless you remove these two lines first (see below).

See the [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html) for driver setup.

On SELinux hosts (e.g. Fedora), rootless Podman is blocked from accessing `/dev/dri` by default even with `AddDevice=`. Allow it once:

```bash
sudo setsebool -P container_use_devices=true
```

> [!NOTE]
> This is a host-wide boolean, not scoped to one container — it applies to every rootless Podman container on the machine.

**To disable GPU passthrough** (e.g. to force software encoding, or the container otherwise shouldn't touch `/dev/dri`), remove both lines from `containers/stubs/frankenphp-octane/quadlets/horizon.quadlets` (and `development/quadlets/horizon.quadlets` if you use that preset), then re-render and reinstall:

```bash
php artisan podman:generate frankenphp-octane
vendor/bin/lpod install frankenphp-octane/horizon.quadlets --replace
```

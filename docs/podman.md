---
title: Podman Quadlet
sidebar_position: 5
tags:
    - podman
    - quadlet
    - containers
    - systemd
---

# Podman Quadlet

**stry** runs as [Podman Quadlet](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html) units: config files that let systemd run and manage each container. The units are generated with [foxws/laravel-podman](https://github.com/foxws/laravel-podman), and you manage them day to day with its companion CLI, [`lpod`](https://github.com/foxws/lpod). For general topics such as customizing presets, secrets or setting up without PHP on the host, see the docs of those two projects. This page only covers what is specific to **stry**.

## Prerequisites

- Linux with systemd, rootless or system-wide
- [Podman 5.3+](https://podman.io/) with the `quadlet` CLI plugin (check that `podman quadlet --help` works)
- [`lpod`](https://github.com/foxws/lpod) v2.2.0 or later. Install it once per host, and upgrade it later with `lpod self-update`. It's a bash script with no dependencies:

    ```bash
    curl -fsSL https://github.com/foxws/lpod/releases/latest/download/install.sh | bash
    ```

## Presets

Each preset is a set of templates in `containers/stubs/`. You can customize one preset without affecting the others (see [Customizing](https://github.com/foxws/laravel-podman/blob/main/docs/customizing.md)):

| Preset         | Purpose                                                                                |
| -------------- | -------------------------------------------------------------------------------------- |
| `production`   | The application and its services, see the table below                                  |
| `ondemand`     | Starts the app on its first request, and lets it and its services sleep when idle      |
| `development`  | The same services, running against your local code (see [Development](development.md)) |
| `s3`           | RustFS bucket and CORS setup (see [S3](s3.md))                                         |
| `devcontainer` | VS Code Dev Containers image (see [Development](development.md))                       |

`production` installs these services. Their names start with `stry` by default; you can change that prefix with `PODMAN_QUADLET_PREFIX`, which defaults to `APP_NAME`.

| Unit               | Role                                       |
| ------------------ | ------------------------------------------ |
| `stry`             | Web server for the app (Octane), port 8000 |
| `stry-pgsql`       | PostgreSQL                                 |
| `stry-valkey`      | Cache and queue backend                    |
| `stry-horizon`     | Queue worker                               |
| `stry-reverb`      | WebSocket server                           |
| `stry-schedule`    | Scheduler                                  |
| `stry-inertia-ssr` | Inertia server-side rendering              |
| `stry-mailpit`     | Catches mail during development            |
| `stry-rustfs`      | S3-compatible storage                      |
| `stry-typesense`   | Search                                     |

Only `stry` opens a port on the host (8000). The other services are only reachable on the internal `{{application}}.network`. A few of them need to be reachable from outside (Reverb, RustFS and the Mailpit web UI); the app's built-in Caddy server forwards requests to them based on the hostname. See [Reverse Proxy](proxy.md).

## Install

```bash
php artisan podman:setup   # generates production, ondemand and s3 into podman/{preset}/

# Install every generated service (see the podman/{preset}/ folder for the full list):
lpod install production/app.quadlets --replace
lpod install production/pgsql.quadlets --replace
# ...

# Then set the secrets for each service:
lpod stry secrets
lpod stry-pgsql secrets
# ...

lpod stry up

# Let the app and its services sleep when idle (skip with PODMAN_ONDEMAND_ENABLED=false):
lpod install ondemand/stry-ondemand.socket --replace
lpod idle enable stry
```

`lpod idle enable` runs the [idle check](https://github.com/foxws/laravel-podman/blob/main/docs/ondemand.md#the-idle-check) every minute. Once the app is asleep and has no work left, it stops Horizon and the scheduler timer, so the database and other services can sleep too.

See the package's [Quick Start](https://github.com/foxws/laravel-podman#quick-start) for the full steps.

:::note
`php artisan podman:setup` and `podman:generate` need `foxws/laravel-podman`, and so does `php artisan podman:idle`, which the idle check runs inside the containers. It's a regular dependency, so `composer install --no-dev` keeps it. `lpod` doesn't need it; it's the standalone tool from [Prerequisites](#prerequisites). On a host without PHP, either generate the files elsewhere and copy the `podman/` folder over, or run `lpod setup` to generate them inside a temporary container. See [Setting up without PHP on the host](https://github.com/foxws/laravel-podman/blob/main/docs/host-setup.md) for both, and [Production Setup](production.md) for the full **stry** walkthrough.
:::

In the `production` preset, the app container uses a pre-built image instead of building one locally. CI builds it and publishes it to `ghcr.io/francoism90/stry`, and Podman pulls it from there. To use your own registry, set `PODMAN_IMAGE_REGISTRY` in `.env`.

## Day to day

```bash
lpod stry up                    # start
lpod stry shell                 # open a shell
lpod stry artisan migrate       # run Artisan
systemctl --user status stry    # or use systemctl and journalctl directly
journalctl --user -u stry -f
```

See [CLI Interaction](interaction.md) for stry's own Artisan commands, and the [`lpod` docs](https://github.com/foxws/lpod) for all `lpod` commands (`secrets`, `remove`, `list`, `print`, `uninstall` and more).

## Tuning & hardware acceleration

Resource limits such as `Memory=` and `ShmSize=` are set in `containers/stubs/production/quadlets/*.quadlets`. After changing them, generate the files again (`php artisan podman:generate production`) and reinstall the service (`lpod install ... --replace`).

The app image includes VA-API drivers. By default, `horizon.quadlets` gives `stry-horizon` access to `/dev/dri` for hardware-accelerated transcoding:

```ini
[Container]
AddDevice=/dev/dri:/dev/dri
GroupAdd=keep-groups
```

:::note
`/dev/dri` must exist on the host. On a machine without a GPU, including most cloud and CI machines, `stry-horizon` won't start until you remove these two lines (see below).
:::

See the [FFmpeg hardware acceleration docs](https://trac.ffmpeg.org/wiki/HWAccelIntro) for setting up the drivers.

On hosts with SELinux, such as Fedora, rootless Podman can't access `/dev/dri` by default, even with `AddDevice=`. Allow it once with:

```bash
sudo setsebool -P container_use_devices=true
```

:::note
This setting applies to the whole host, not just one container. Every rootless Podman container on the machine gets access to devices.
:::

**To turn off GPU access**, for example to force software encoding, remove both lines from `containers/stubs/production/quadlets/horizon.quadlets` (and from `development/quadlets/horizon.quadlets` if you use that preset). Then generate the files again and reinstall:

```bash
php artisan podman:generate production
lpod install production/horizon.quadlets --replace
```

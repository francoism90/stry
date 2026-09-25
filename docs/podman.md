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
- [`lpod`](https://github.com/foxws/lpod). Install it once per host; it's a bash script with no dependencies:

    ```bash
    curl -fsSL -o ~/.local/bin/lpod https://github.com/foxws/lpod/releases/latest/download/lpod
    chmod +x ~/.local/bin/lpod
    ```

## Presets

Each preset is a set of templates in `containers/stubs/`. You can customize one preset without affecting the others (see [Customizing](https://github.com/foxws/laravel-podman/blob/main/docs/customizing.md)):

| Preset              | Purpose                                                                                |
| ------------------- | -------------------------------------------------------------------------------------- |
| `frankenphp-octane` | The application and its services, see the table below                                  |
| `development`       | The same services, running against your local code (see [Development](development.md)) |
| `s3`                | RustFS bucket and CORS setup (see [S3](s3.md))                                         |
| `devcontainer`      | VS Code Dev Containers image (see [Development](development.md))                       |

`frankenphp-octane` installs these services. Their names start with `stry` by default; you can change that prefix with `PODMAN_QUADLET_PREFIX`, which defaults to `APP_NAME`.

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
php artisan podman:setup   # generates every preset above into podman/{preset}/

# Install every generated service (see the podman/{preset}/ folder for the full list):
lpod install frankenphp-octane/app.quadlets --replace
lpod install frankenphp-octane/pgsql.quadlets --replace
# ...

# Then set the secrets for each service:
lpod stry secrets
lpod stry-pgsql secrets
# ...

lpod stry up
```

See the package's [Quick Start](https://github.com/foxws/laravel-podman#quick-start) for the full steps.

:::note
`php artisan podman:setup` and `podman:generate` need `foxws/laravel-podman`. That's a `require-dev` package, so it isn't installed when you run `composer install --no-dev`, as you would in production. `lpod` doesn't need it; it's the standalone tool from [Prerequisites](#prerequisites). On a host without PHP, either generate the files elsewhere and copy the `podman/` folder over, or run `lpod setup` to generate them inside a temporary container. See [Setting up without PHP on the host](https://github.com/foxws/laravel-podman/blob/main/docs/host-setup.md) for both, and [Production Setup](production.md) for the full **stry** walkthrough.
:::

In the `frankenphp-octane` preset, the app container uses a pre-built image instead of building one locally. CI builds it and publishes it to `ghcr.io/francoism90/stry`, and Podman pulls it from there. To use your own registry, set `PODMAN_IMAGE_REGISTRY` in `.env`.

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

Resource limits such as `Memory=` and `ShmSize=` are set in `containers/stubs/frankenphp-octane/quadlets/*.quadlets`. After changing them, generate the files again (`php artisan podman:generate frankenphp-octane`) and reinstall the service (`lpod install ... --replace`).

The app image includes VA-API drivers. By default, `horizon.quadlets` gives `stry-horizon` access to `/dev/dri` for hardware-accelerated transcoding:

```ini
[Container]
AddDevice=/dev/dri:/dev/dri
GroupAdd=keep-groups
```

:::note
`/dev/dri` must exist on the host. On a machine without a GPU, including most cloud and CI machines, `stry-horizon` won't start until you remove these two lines (see below).
:::

See the [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html) for setting up the drivers.

On hosts with SELinux, such as Fedora, rootless Podman can't access `/dev/dri` by default, even with `AddDevice=`. Allow it once with:

```bash
sudo setsebool -P container_use_devices=true
```

:::note
This setting applies to the whole host, not just one container. Every rootless Podman container on the machine gets access to devices.
:::

**To turn off GPU access**, for example to force software encoding, remove both lines from `containers/stubs/frankenphp-octane/quadlets/horizon.quadlets` (and from `development/quadlets/horizon.quadlets` if you use that preset). Then generate the files again and reinstall:

```bash
php artisan podman:generate frankenphp-octane
lpod install frankenphp-octane/horizon.quadlets --replace
```

## Storage sizing (tmpfs)

By default, `horizon.quadlets` mounts `/cache` from the `{app}-cache` volume, which is stored on disk. `laravel-shaka` and `laravel-streamer` use it as temporary space while packaging a video, then upload the result to S3. Nothing in `/cache` needs to survive a restart, so you can use a `tmpfs` mount instead. That keeps this work in RAM: it's faster and doesn't wear out your SSD, but it uses memory that every other service on the machine also needs.

The template already contains a commented-out `Tmpfs=` line, next to the `Volume=` line it replaces:

```ini
# Tmpfs=/cache:rw,size=12g,mode=1777
Volume={{application}}-cache.volume:/cache:rw,z
```

Choosing the size means balancing it against everything else on the machine: PostgreSQL, Valkey, Typesense, RustFS and the app itself. If you set `size=` larger than the memory that's actually free, it doesn't protect you at all (see [laravel-shaka's storage guards docs](https://github.com/foxws/laravel-shaka/blob/main/docs/CONFIGURATION.md#storage-space-guards) for why).

The values below are starting points for running everything on a single machine. Measure what your own jobs really use (run `du -sh` on the temporary folder of a finished job) and adjust:

| RAM   | tmpfs `size=` | Min free  | maxProcesses\* |
| ----- | ------------- | --------- | -------------- |
| 8 GB  | -             | -         | -              |
| 16 GB | `6g`          | `1 GiB`   | 3              |
| 24 GB | `10g`         | `1.5 GiB` | 5              |
| 32 GB | `14g`         | `2 GiB`   | 7              |

With 8 GB of RAM, skip tmpfs and keep the disk volume. There's rarely enough memory left over next to PostgreSQL, Valkey, Typesense and RustFS.

\* Assumes about 1.5 GB of temporary space per packaging job running at the same time (with `temporary_files_size_multiplier` applied). Recalculate this for the renditions you actually produce, and set it in the queue's supervisor config, so that the number of workers times the largest expected job stays well below the tmpfs size.

To use it, uncomment `Tmpfs=`, remove the `Volume=` line below it, and set these values. `PACKAGER_TEMPORARY_MIN_FREE` is in bytes, not GiB:

```env
PACKAGER_TEMPORARY_FILES_ROOT=/cache/temp/packager
PACKAGER_TEMPORARY_MIN_FREE=1610612736   # 1.5 GiB, for the 24 GB row above
```

Then generate the files again and reinstall, as above.

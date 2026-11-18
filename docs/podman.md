---
title: Podman Quadlet
section: Configuration
order: 2
tags:
    - podman
    - quadlet
    - containers
    - systemd
---

# Podman Quadlet

**stry** runs as [Podman Quadlet](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html) units: config files that let systemd run and manage each container. The units are plain Quadlet files in this repository, ready to install as they are: there's nothing to generate and no placeholders to fill in. Install them with [Pods](https://github.com/foxws/pods), with `podman quadlet install`, or copy them by hand.

## Prerequisites

- Linux with systemd, rootless
- [Podman 6+](https://podman.io/) (check that `podman quadlet --help` works)
- Linger turned on for your user, so the services start at boot: `sudo loginctl enable-linger "$USER"`
- Optional: [`lpod`](https://github.com/foxws/lpod), for day-to-day commands such as `lpod stry artisan` and `lpod stry-pgsql secrets`. It's a bash script with no dependencies:

    ```bash
    curl -fsSL https://github.com/foxws/lpod/releases/latest/download/install.sh | bash
    ```

## The units

| Folder                            | Purpose                                                                                     |
| --------------------------------- | ------------------------------------------------------------------------------------------- |
| `containers/quadlets/production`  | The application and its services, from the image CI publishes to `ghcr.io/francoism90/stry` |
| `containers/quadlets/development` | The same services, running against your checkout (see [Development](development.md))        |
| `containers/devcontainer`         | A [Dev Containers](https://containers.dev/) config (see [Development](development.md))      |
| `containers/runtimes`             | The Containerfiles of the app image                                                         |

`production` installs these units:

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

No unit publishes a port on the host: every service, the app included, is only reachable on the internal `stry.network` (`ExposeHostPort=`). That keeps the units from clashing with other apps or other users on the same host. To reach the app, either run a reverse proxy container that joins `stry.network` (a proxy app for Pods is planned), or publish the app's port with a drop-in, for example `~/.config/containers/systemd/stry.container.d/port.conf`:

```ini
[Container]
PublishPort=8000:8000
```

Then point your reverse proxy at that port. A few services need to be reachable from the browser (Vite, Reverb, RustFS and the Mailpit web UI); the app's built-in Caddy server forwards requests to them based on the hostname. See [Reverse Proxy](proxy.md).

The units don't hard-code anything about your host:

- **Your user.** The app containers run as your own user through `UserNS=keep-id`, with `%U` and `%G` (systemd's specifiers for your user and group ID) wherever an ID is needed.
- **Your library.** `/media`, `/import` and `/exports` are the `stry-videos`, `stry-import` and `stry-exports` volumes. Each one is a folder on the host: `/srv/library/videos`, `/srv/library/import` and `/srv/library/exports` in production, which you create once (see below), and `storage/app/library/*` in your checkout in development, which the volumes create themselves. To use another folder, give its volume a drop-in (see [Another library folder](#another-library-folder)).
- **Your secrets.** Passwords and the `.env` file are Podman secrets, which the units read with `Secret=`.

## Install

### With Pods

In [Pods](https://github.com/foxws/pods), add an application from Git with the repository `https://github.com/francoism90/stry.git` and `main` or a release tag. Pods reads stry's `pods.yml` and offers its two sources: choose **production** (or **development**, which needs a checkout in `~/projects/stry` on the host).

Pods then shows the units and asks for the secrets they use. Paste your `.env` file as `stry-env`: Pods fills the database, Typesense and RustFS secrets in from its `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `TYPESENSE_API_KEY`, `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`, so the app and the services use the same values. It creates the secrets, then installs and starts the units.

`pods.yml` only tells Pods how to offer stry; the units are the same ones you'd install by hand.

### By hand

```bash
git clone https://github.com/francoism90/stry.git ~/projects/stry
cd ~/projects/stry

# If your library isn't in /srv/library, add the drop-ins from
# "Another library folder" in docs/podman.md first.
podman quadlet install --application=stry containers/quadlets/production

# Create the secrets the units use. "lpod SERVICE secrets" asks for each
# value; without lpod, use "podman secret create NAME -" with the value on stdin.
podman secret create stry-env .env
lpod stry-pgsql secrets
lpod stry-typesense secrets
lpod stry-rustfs secrets

systemctl --user start stry
```

`stry` starts the services it needs. Then prepare the database and search, and create an admin:

```bash
lpod stry artisan app:install
```

See [Production Setup](production.md) for the full walkthrough. Once the units are installed, the checkout isn't needed on the server anymore.

## On-demand services

The app can start on its first request and stop after 10 minutes without traffic, so it and the services it needs only use memory while someone uses **stry**. This is optional. [lpod](https://github.com/foxws/lpod) writes the units from the settings in the `ondemand:` section of `pods.yml`:

- a socket on port 8000 that starts the app on the first request, and a proxy that forwards to it on `127.0.0.1:18000` and exits after 10 minutes without connections;
- an idle check that stops Horizon once the app sleeps and `php artisan app:idle` finds no work left, so the database and cache can sleep too;
- a timer that starts the scheduler only in the minutes a task is due, as a `schedule:run`.

Turn it on for an installed `production` or `development` app:

```bash
lpod stry ondemand enable --json \
    --port 8000 --app-port 8000 --idle-timeout 10min \
    --worker stry-horizon \
    --idle-container stry-horizon --idle-user docker \
    --idle-command "php -d variables_order=EGPCS /app/artisan app:idle" \
    --schedule stry-schedule --schedule-at "*:00" --schedule-at 01:00 --schedule-at 02:00 \
    --schedule-env "APP_COMMAND=php -d variables_order=EGPCS /app/artisan schedule:run" \
    --schedule-env APP_OPTIMIZE=false
```

Pick another `--port` when 8000 is taken on the host. Your proxy then sends stry's hostname to that port on the host, not to the `stry` container. The Pods proxy does that by itself: while stry is on demand, lpod sends its main route to `host.containers.internal:PORT`. The socket must listen on an address the proxy container reaches, so don't pass `--listen 127.0.0.1` then. `lpod stry ondemand status --json` shows the settings and whether the app is awake, and `lpod stry ondemand disable --json` turns it off again and starts the app.

A sleeping app skips nothing on the schedule: the scheduler timer still starts the database and cache for the minutes a task is due. Keep `schedule.at` in `pods.yml` (and `--schedule-at`) in sync when you change the schedule, then run `enable` again. The first request after a sleep waits for every service to start.

In development, only page loads count as traffic: Vite's hot reload talks to its own container, so the app falls asleep while you edit. Pass a longer `--idle-timeout`, such as `60min`, or leave on-demand off on a development machine.

## Day to day

```bash
lpod stry up                    # start
lpod stry shell                 # open a shell
lpod stry artisan migrate       # run Artisan
systemctl --user status stry    # or use systemctl and journalctl directly
journalctl --user -u stry -f
```

See [CLI Interaction](interaction.md) for stry's own Artisan commands, and the [`lpod` docs](https://github.com/foxws/lpod) for all `lpod` commands.

## Changing a unit

To change a unit for your host, such as a memory limit or a port, add a drop-in instead of editing the installed file, so a reinstall keeps your change. For example, `~/.config/containers/systemd/stry-horizon.container.d/memory.conf`:

```ini
[Container]
Memory=4g
```

Then run `systemctl --user daemon-reload` and restart the service. Keys that can be repeated, such as `PublishPort=`, add to the unit's values; an empty `PublishPort=` first clears them.

## Storage, ownership & SELinux

The app, Horizon and the scheduler share these mounts:

| Mount      | Source                        | Holds                                                                      |
| ---------- | ----------------------------- | -------------------------------------------------------------------------- |
| `/media`   | `/srv/library/videos` (host)  | Your video library, `Device=` in `stry-videos.volume`                      |
| `/import`  | `/srv/library/import` (host)  | Files waiting to be imported, `Device=` in `stry-import.volume`            |
| `/exports` | `/srv/library/exports` (host) | Videos exported from the library, `Device=` in `stry-exports.volume`       |
| `/config`  | `stry-config` volume          | Configuration                                                              |
| `/data`    | `stry-data` volume            | App data                                                                   |
| `/cache`   | `stry-cache` volume           | Packaged stream segments, and media temporary files in `/cache/temp/media` |

**Ownership.** The containers run as root only long enough for the entrypoint to switch to the `docker` user (your `PUID`/`PGID`). With `UserNS=keep-id`, your host user and the container user have the same ID, so files line up on both sides. Don't add Podman's `U` option to these mounts: it hands the volume to the image's user, which is root, on every start. With [on-demand services](#on-demand-services) that's every time the app wakes up, and a root-owned `/cache/temp` breaks streaming (see [Troubleshooting](#troubleshooting)). On each start, the entrypoint gives `/config`, `/data`, `/cache`, `/cache/temp` and `/cache/temp/media` back to the `docker` user if something else owns them.

**SELinux.** On hosts with SELinux, such as Fedora, a container can only use a mount that has a container label:

- The named volumes (`/config`, `/data`, `/cache`) have no flags. Podman's SELinux policy already labels named volumes for containers, so `z` would only walk the whole volume on every start. Never use `Z` (a private label) on them: `/cache` is shared by the app, Horizon and the scheduler, and the last one to start would lock the others out. If a volume ends up with the wrong label, for example after copying a backup into it from the host, run `restorecon -R ~/.local/share/containers/storage/volumes`.
- `/media`, `/import` and `/exports` have no `z`. Relabeling would walk your whole library every time a container starts, and the scheduler starts one every minute. It would also change the labels of these folders on the host. Create them and label them once on the host instead:

    ```bash
    sudo mkdir -p /srv/library/videos /srv/library/import /srv/library/exports
    sudo chown -R "$USER:" /srv/library
    sudo semanage fcontext -a -t container_file_t '/srv/library(/.*)?'
    sudo restorecon -R /srv/library
    ```

    If your library lives elsewhere, point the volumes at it (see below) and label those folders instead. Files you add later get the label automatically.

### Another library folder

To keep your library somewhere other than `/srv/library`, give `stry-videos`, `stry-import` and `stry-exports` a drop-in that sets their `Device=`, instead of editing the `.volume` files: an update, with Pods or `podman quadlet install --replace`, replaces those files, but keeps your drop-ins. For example, `~/.config/containers/systemd/stry-videos.volume.d/path.conf`:

```ini
[Volume]
Device=/mnt/videos
```

Do the same for `stry-import.volume.d/` and `stry-exports.volume.d/`, then run `systemctl --user daemon-reload`. A volume that already exists keeps its old folder, so if you change the folder later, stop stry and remove the volume (`podman volume rm stry-videos`) before you start it again. These volumes only point at the folders: removing one never deletes your files.

### Troubleshooting

**Streams fail with `Error opening output /cache/temp/media/…/fragment.mp4: No such file or directory`**: the app can't create its temporary folders, because something running as root owns `/cache/temp`. This happens after a command run with `podman exec` (which runs as root unless you pass `--user`), or on installs that still have `U` on the `/cache` volume. Fix it without restarting:

```bash
podman exec systemd-stry chown -R docker:docker /cache/temp
```

When you run your own commands with `podman exec`, pass `--user docker` so they don't leave root-owned files behind.

**Permission denied on `/media`, `/import` or `/exports`**: the folders aren't labeled for containers yet. Run the `semanage` and `restorecon` commands above.

## Tuning & hardware acceleration

Resource limits such as `Memory=` and `ShmSize=` are set in the units in `containers/quadlets/production/`. To change them for your host, add a drop-in (see [Changing a unit](#changing-a-unit)).

The app image includes the VA-API drivers for Intel and AMD, and the Quick Sync runtime for Intel. By default, `stry-horizon.container` (and `stry.container`) gives `stry-horizon` access to `/dev/dri` for hardware-accelerated transcoding:

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

**To turn off GPU access**, for example to force software encoding, clear both lines with a drop-in for `stry-horizon` and `stry`, such as `~/.config/containers/systemd/stry-horizon.container.d/no-gpu.conf`:

```ini
[Container]
AddDevice=
GroupAdd=
```

Then run `systemctl --user daemon-reload` and restart the services.

### NVIDIA

:::warning
This is untested. It's what NVIDIA GPUs probably need, and we're waiting for feedback from someone who has tried it.
:::

The app image has no NVIDIA libraries, and it doesn't need them. FFmpeg loads `libcuda`, `libnvcuvid` (NVDEC) and `libnvidia-encode` (NVENC) when it runs. These come with the host's NVIDIA driver and must match its version, so the [NVIDIA Container Toolkit](https://docs.nvidia.com/datacenter/cloud-native/container-toolkit/latest/install-guide.html) mounts them into the container. Install the toolkit, then generate the CDI spec once on the host (and again after every driver update):

```bash
sudo nvidia-ctk cdi generate --output=/etc/cdi/nvidia.yaml
```

Replace the `/dev/dri` device with the GPU in a drop-in for `stry-horizon` and `stry`, such as `~/.config/containers/systemd/stry-horizon.container.d/nvidia.conf`:

```ini
[Container]
AddDevice=
AddDevice=nvidia.com/gpu=all
```

Set `MEDIA_LADDER_HARDWARE=nvenc` in `.env` and store it as the `stry-env` secret again, then run `systemctl --user daemon-reload` and restart the services.

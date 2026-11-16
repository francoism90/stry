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
| `containers/ondemand`             | Optional: starts the app on its first request, and lets it and its services sleep when idle |
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

Only `stry` opens a port on the host (8000). The other services are only reachable on the internal `stry.network`. A few of them need to be reachable from outside (Reverb, RustFS and the Mailpit web UI); the app's built-in Caddy server forwards requests to them based on the hostname. See [Reverse Proxy](proxy.md).

The units don't hard-code anything about your host:

- **Your user.** The app containers run as your own user through `UserNS=keep-id`, with `%U` and `%G` (systemd's specifiers for your user and group ID) wherever an ID is needed.
- **Your library.** `/media`, `/import` and `/exports` are the `stry-videos`, `stry-import` and `stry-exports` volumes. Each one is a folder on the host, `/srv/library/videos`, `/srv/library/import` and `/srv/library/exports` by default. To use another folder, change `Device=` in its `.volume` file before you install. See [Storage, ownership & SELinux](#storage-ownership--selinux).
- **Your secrets.** Passwords and the `.env` file are Podman secrets, which the units read with `Secret=`.

## Install

### With Pods

In [Pods](https://github.com/foxws/pods), add an application from Git:

- **Repository:** `https://github.com/francoism90/stry.git`
- **Branch or tag:** `main`, or a release tag
- **Directory:** `containers/quadlets/production`

Pods shows the units, asks for the value of every secret they use, creates the secrets and then installs and starts the units. Use your `.env` file as the value of `stry-env`, and the matching keys in it for the others (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `TYPESENSE_API_KEY`, `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`), so the app and the services use the same values.

### By hand

```bash
git clone https://github.com/francoism90/stry.git ~/projects/stry
cd ~/projects/stry

# Change Device= in containers/quadlets/production/stry-*.volume first if
# your library isn't in /srv/library.
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

The app can start on its first request and stop after 10 minutes without traffic, so it and the services it needs only use memory while someone uses **stry**. This is optional, and the units in `containers/ondemand/` add it to an installed `production` or `development` app:

| File                                               | Role                                                                                                |
| -------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| `systemd/stry-ondemand.socket`                     | Listens on port 8000 and starts the app on the first request                                        |
| `systemd/stry-ondemand.service`                    | Forwards the socket to the app, and exits after 10 minutes without connections                      |
| `systemd/stry-idle.service`, `stry-idle.timer`     | The idle check: once the app sleeps and `php artisan app:idle` finds no work left, it stops Horizon |
| `systemd/stry-schedule.timer`                      | Starts the scheduler only in the minutes a task is due                                              |
| `quadlets/stry.container.d/ondemand.conf`          | Moves the app to `127.0.0.1:18000`, and lets it stop once the socket no longer needs it             |
| `quadlets/stry-schedule.container.d/ondemand.conf` | Turns the scheduler into a `schedule:run` that the timer starts                                     |

Install them, then switch from the app's own port to the socket:

```bash
cp containers/ondemand/systemd/* ~/.config/systemd/user/
cp -r containers/ondemand/quadlets/. ~/.config/containers/systemd/
systemctl --user daemon-reload
systemctl --user stop stry
systemctl --user enable --now stry-ondemand.socket
```

To turn it off again, disable the socket, remove these files and start `stry` again:

```bash
systemctl --user disable --now stry-ondemand.socket
rm ~/.config/systemd/user/stry-{ondemand,idle,schedule}.* ~/.config/containers/systemd/{stry,stry-schedule}.container.d/ondemand.conf
systemctl --user daemon-reload
systemctl --user start stry
```

A sleeping app skips nothing on the schedule: the scheduler timer still starts the database and cache for the minutes a task is due. Keep its `OnCalendar=` lines in sync when you change the schedule (see the comments in `stry-schedule.timer`). The first request after a sleep waits for every service to start. To change the idle timeout, change `--exit-idle-time=` in `stry-ondemand.service`.

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

    If your library lives elsewhere, change `Device=` in `stry-videos.volume`, `stry-import.volume` and `stry-exports.volume` before you install, and label those folders instead. These volumes only point at the folders: removing one, for example with `podman volume rm stry-videos` after changing its folder, never deletes your files. Files you add later get the label automatically.

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

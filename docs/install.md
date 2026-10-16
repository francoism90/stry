---
title: Installer
section: Getting Started
order: 2
tags:
    - production
    - installation
    - podman
---

# Installer

The installer sets **stry** up on a server in your browser. It runs once as a container, installs the app as rootless Podman Quadlet services, and is removed when you stop it. It's built on [Laravel Installer](https://github.com/foxws/laravel-installer), and you don't need PHP, Composer or [`lpod`](https://github.com/foxws/lpod) on the server.

To set things up by hand instead, see [Production Setup](production.md).

## What it does

1. **Requirements**: checks Podman, cgroups v2 and subordinate IDs through the Podman socket.
2. **Secrets**: creates the Podman secrets the services use. `stry-env` comes from your `.env` file. The database, Typesense and RustFS secrets come from the matching keys in it (`DB_*`, `TYPESENSE_API_KEY`, `AWS_*`), so the app and the services use the same values.
3. **Services**: renders the presets in `PODMAN_DEFAULT_PRESETS` into your checkout, installs them as Quadlets and starts them.
4. **Install**: runs `php artisan app:install`: the migrations, the roles and permissions, and the search collections.
5. **Admin account**: creates the super-admin you sign in with, unless one exists.

Every step is safe to run again.

## Before you start

Get the code and create your `.env`:

```bash
cd ~
git clone https://github.com/francoism90/stry.git
cd stry
cp .env.example .env
```

In `.env`, set at least:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://stry.example.com
APP_KEY=base64:...            # openssl rand -base64 32

DB_DATABASE=stry
DB_USERNAME=stry
DB_PASSWORD=...               # openssl rand -hex 32

TYPESENSE_API_KEY=...         # openssl rand -hex 32
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...

PODMAN_DEFAULT_PRESETS=production,ondemand
```

Create the media folders. They default to `/srv/library`, or set `PODMAN_VIDEOS_PATH`, `PODMAN_IMPORT_PATH` and `PODMAN_EXPORTS_PATH` for your own. On SELinux hosts, such as Fedora, label them too:

```bash
sudo mkdir -p /srv/library/videos /srv/library/import /srv/library/exports
sudo chown -R "$USER:" /srv/library
sudo semanage fcontext -a -t container_file_t '/srv/library(/.*)?'
sudo restorecon -R /srv/library
```

Then start the Podman socket, turn on linger so the services start at boot, and create the folders the installer writes the units to:

```bash
systemctl --user enable --now podman.socket
loginctl enable-linger "$USER"
mkdir -p ~/.config/containers/systemd ~/.config/systemd/user
```

## Run the installer

Build the installer image from your checkout:

```bash
podman build --pull=newer -t stry-installer installer/
```

Then run it:

```bash
podman run --rm -it \
    --userns=keep-id \
    --security-opt label=disable \
    -v "$XDG_RUNTIME_DIR/podman/podman.sock:/run/podman/podman.sock" \
    -v "$HOME/stry:/workspace" \
    -e PODMAN_WORKING_PATH="$HOME/stry" \
    -e PODMAN_QUADLET_UID="$(id -u)" \
    -e PODMAN_QUADLET_GID="$(id -g)" \
    -v "$HOME/.config/containers/systemd:/run/installer/quadlets" \
    -v "$HOME/.config/systemd/user:/run/installer/units" \
    -v "$XDG_RUNTIME_DIR/bus:/run/installer/bus" \
    -e DBUS_SESSION_BUS_ADDRESS=unix:path=/run/installer/bus \
    -p 127.0.0.1:8085:8085 \
    localhost/stry-installer
```

It prints a link that works once, such as `http://127.0.0.1:8085/?token=…`. On a server, open it through an SSH tunnel from your own machine: `ssh -L 8085:127.0.0.1:8085 your-server`.

To open it on your network instead, replace the `-p` line with the server's address, and tell the installer that address:

```bash
    -p 192.168.1.100:8085:8085 -e INSTALLER_HOST=192.168.1.100 \
```

It then serves HTTPS and prints `https://192.168.1.100:8085/?token=…`. The certificate comes from the installer's own internal CA, so your browser warns about it once.

| Option                                  | Why                                                                                          |
| --------------------------------------- | -------------------------------------------------------------------------------------------- |
| `--userns=keep-id`                      | Runs the installer as your own user, so systemd accepts it and the files it writes are yours |
| `label=disable`                         | Lets it use the Podman socket and the session bus on SELinux hosts                           |
| `podman.sock`                           | Checks the host, creates the secrets and runs commands in the app's container                |
| `~/stry` at `/workspace`                | Where the Quadlet files are rendered (`~/stry/podman/`) and the `.env` is read from          |
| `PODMAN_WORKING_PATH`                   | The checkout's path on the host, which is written into the Quadlet files                     |
| `PODMAN_QUADLET_UID` / `GID`            | Your user and group IDs, for the Quadlet files                                               |
| `containers/systemd` and `systemd/user` | Where the Quadlet files, the on-demand socket and the timers are installed                   |
| `bus` and `DBUS_SESSION_BUS_ADDRESS`    | Lets the installer reload systemd and start the services                                     |

## When it's done

Stop the installer with Ctrl+C. It's removed when it stops, and leaves nothing behind but the services. Check them with:

```bash
systemctl --user status stry
journalctl --user -u 'stry*' -f
```

Then point your reverse proxy at the app's port `8000` (see [Reverse Proxy](proxy.md)), and work through the [security checklist](production.md#security-checklist).

## Development

The installer also sets up a development checkout. Set `APP_ENV=local`, `APP_DEBUG=true` and `PODMAN_DEFAULT_PRESETS=development,ondemand` in its `.env`, run `composer install` in it, and mount it at `/workspace`. The development services build their image from your checkout, mount it into the containers, and don't use Podman secrets. Once the installer is done, finish the setup inside the container, as in [Development Setup](development.md#setup):

```bash
lpod stry-dev shell
php artisan storage:link
pnpm install
```

## Troubleshooting

- **A service failed to start**: run `journalctl --user -u SERVICE -n 50` on the host, fix the problem, then press "Try again".
- **The on-demand socket failed**: another process uses its port, `8000` by default. Set another one with `PODMAN_ONDEMAND_LISTEN` in `.env`, then press "Try again".
- **"No workspace is mounted"**: the checkout isn't mounted at `/workspace`. Check the `-v "$HOME/stry:/workspace"` line.
- **The page shows stry's own startup messages**: the last line of `podman run` names the app's image instead of `stry-installer`.

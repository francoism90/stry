---
name: podman-lpod
description: Run Artisan, Composer, Node, tests and shell commands inside the app's Podman Quadlet containers with lpod, and manage those services (start, stop, install, secrets). Use when the project uses foxws/laravel-podman and you need to run anything in the app container or control its services.
---

# Running commands with lpod

This app runs as [Podman Quadlet](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html) services managed by systemd. [`lpod`](https://github.com/foxws/lpod) is the CLI for them. Run PHP, Composer and Node through `lpod` so they use the container's versions and extensions, not the host's.

## Usage

```bash
lpod SERVICE COMMAND [arguments]
```

- `SERVICE` is the plain service name: the app (the kebab-cased `PODMAN_QUADLET_PREFIX`, which defaults to `APP_NAME`) or a service like `pgsql`. Don't add the `systemd-` prefix; `lpod` does that.
- Unknown commands are passed on to `podman`.
- Run `lpod list` to see the installed services.

## Inside a devcontainer

Check first whether you're in the package's devcontainer: `/run/.containerenv` exists or `REMOTE_CONTAINERS=true` is set, and `command -v lpod` finds nothing. There is no Podman or systemd in there, on purpose. Don't install them, and don't try Podman in Podman or mounting the host's Podman socket.

- Run PHP, Composer, Node and tests directly (`php artisan test`, `composer install`, `pnpm build`). The devcontainer has the same tools and joins the app's `systemd-{application}` network, so the database and cache hosts in `.env` work from it.
- `php artisan podman:generate` and `podman:setup` work there too. The devcontainer sets `PODMAN_WORKING_PATH` to the host path, so the rendered `Volume=` lines point at the host's working copy, not `/app`.
- `lpod`, `podman` (including `podman run` and `podman volume`) and `journalctl` act on the host's services. Don't run them, and don't work around that. List the exact commands for the user to run in a host terminal, from the project folder, and wait for their output.
- An `lpod my-app ...` command in this skill or another one becomes the plain command in the devcontainer: `lpod my-app artisan migrate` is `php artisan migrate`. Lifecycle, install, `secrets`, `idle` and `xdebug` commands stay on the host.

## In the app container

```bash
lpod my-app artisan migrate          # also: art, a
lpod my-app composer require foo/bar
lpod my-app php -v
lpod my-app tinker
lpod my-app debug queue:work         # Artisan with Xdebug enabled
lpod my-app xdebug on | off          # Xdebug for web requests (development image), restarts the app

lpod my-app test                     # php artisan test
lpod my-app pest --filter=UserTest
lpod my-app phpunit
lpod my-app pint

lpod my-app pnpm install             # also: node, npm, yarn, bun, npx, pnpx, bunx
lpod my-app bin phpstan              # ./vendor/bin/phpstan

lpod my-app run CMD                  # any command
lpod my-app shell                    # interactive shell; root-shell for root
```

Commands run as the host user (`APP_USER`, default `$(id -u)`). `lpod` loads `.env` and `.env.$APP_ENV` from the current directory.

## Service lifecycle

```bash
lpod my-app up | down | restart | status
lpod my-app secrets                  # prompt for the unit's Secret= values
lpod my-app open                     # open APP_URL in the browser
```

## Troubleshooting the host

Run `lpod doctor` when services don't start or the proxy doesn't answer. It checks Podman, systemd, linger, subordinate IDs, unprivileged ports, the idle templates, the proxy's certificate, `APP_URL` and failed services, and prints fixes. Fixes with `sudo` are for the user to run.

## Installing rendered services

The Artisan commands only render files into `podman/` (don't commit it). `lpod` installs them:

```bash
php artisan podman:generate development
lpod install development/app.quadlets --replace
lpod reload                          # systemctl daemon-reload
lpod print my-app                    # show the generated systemd unit
```

After changing a `.quadlets` file or its template, regenerate and reinstall with `--replace`, then restart the service.

## On-demand idle check

```bash
lpod idle enable my-app              # stop workers and the scheduler timer once the sleeping app is idle
lpod idle my-app                     # run the check once
lpod idle disable my-app
journalctl --user -u lpod-idle@my-app
```

Needs `lpod` v2.2.0 or later. Extra workers to check and stop go in `LPOD_IDLE_WORKERS`, set in a drop-in on `lpod-idle@my-app.service`.

## Installing and upgrading lpod

```bash
curl -fsSL https://github.com/foxws/lpod/releases/latest/download/install.sh | bash
lpod --version
lpod self-update                     # v2.2.0 and later
```

## Destructive commands

`lpod remove NAME` and `lpod uninstall APPLICATION` delete the service's Podman volumes (databases, uploads, search indexes). There is no undo. Never run them without the user's explicit confirmation. Offer a backup first:

```bash
podman volume export systemd-my-app-pgsql -o pgsql-backup.tar
lpod my-app-pgsql run sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' > backup.sql
```

Check the real volume name with `podman volume ls` before exporting.

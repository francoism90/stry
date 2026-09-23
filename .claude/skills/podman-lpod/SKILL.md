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

## In the app container

```bash
lpod my-app artisan migrate          # also: art, a
lpod my-app composer require foo/bar
lpod my-app php -v
lpod my-app tinker
lpod my-app debug queue:work         # Artisan with Xdebug enabled

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

## Installing rendered services

The Artisan commands only render files into `podman/` (don't commit it). `lpod` installs them:

```bash
php artisan podman:generate development
lpod install development/app.quadlets --replace
lpod reload                          # systemctl daemon-reload
lpod print my-app                    # show the generated systemd unit
```

After changing a `.quadlets` file or its template, regenerate and reinstall with `--replace`, then restart the service.

## Destructive commands

`lpod remove NAME` and `lpod uninstall APPLICATION` delete the service's Podman volumes (databases, uploads, search indexes). There is no undo. Never run them without the user's explicit confirmation. Offer a backup first:

```bash
podman volume export systemd-my-app-pgsql -o pgsql-backup.tar
lpod my-app run pg_dump -U postgres -d laravel > backup.sql
```

Check the real volume name with `podman volume ls` before exporting.

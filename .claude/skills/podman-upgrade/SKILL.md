---
name: podman-upgrade
description: Upgrade foxws/laravel-podman to v5 (from v4, or from v3 through v4). v5 lets services sleep with the on-demand app, renames the frankenphp-octane preset to production, moves the on-demand socket into an "ondemand" preset with an idle check (podman:idle), and must be installed without --dev. v4 made on-demand the default (systemd/ folder, BindsTo= to PartOf=, app health check, frankenphp-octane (now production) worker and scheduler changes). Covers updating published presets in containers/stubs without losing customizations, and reinstalling with lpod. Use when upgrading the package, or when published presets still use BindsTo=, lack StopWhenUnneeded={{ondemand}}, keep ondemand.socket in their systemd/ folder, or a frankenphp-octane preset still exists.
---

# Upgrading laravel-podman

Check the installed major with `composer show foxws/laravel-podman`. Coming from v3, do "From v3 to v4" first, then "From v4 to v5".

The human guide is [UPGRADING.md](https://github.com/foxws/laravel-podman/blob/main/UPGRADING.md). It isn't shipped in `vendor/`, and these notes cover the same steps.

## From v4 to v5

v5 lets the whole stack sleep. Once the app is idle and `podman:idle` finds no work in progress, the idle check stops the queue workers and the scheduler timer. Every service with `StopWhenUnneeded={{ondemand}}` then stops as soon as no running unit `Requires=` or `Wants=` it. The next request starts them again.

### 1. Update the package and config

- Move the package out of `require-dev`: `composer remove foxws/laravel-podman --dev` and `composer require foxws/laravel-podman:^5.0`. Production images are built with `--no-dev`, and `podman:idle` has to exist inside them.
- If `config/podman.php` is published, add the `idle` block from `vendor/foxws/laravel-podman/config/podman.php`.
- If `presets` or `PODMAN_DEFAULT_PRESETS` is set, add `ondemand` to it.

### 2. Ask before letting services sleep

Services follow `PODMAN_ONDEMAND_ENABLED`, like the app. Ask the user whether that's fine. While the stack sleeps:
- scheduled tasks don't run;
- host ports (a database client, an S3 URL) don't wake a service.

To keep everything running, set `PODMAN_ONDEMAND_ENABLED=false`. To keep a single service running, set `StopWhenUnneeded=no` in its published quadlet.

### 3. Rename `frankenphp-octane` to `production`

- If `containers/stubs/frankenphp-octane` (or that folder under `stubs_path`) exists, rename it to `production` with `git mv`.
- Replace `frankenphp-octane` with `production` in `presets`/`PODMAN_DEFAULT_PRESETS`, CI workflows (e.g. `podman/frankenphp-octane/runtimes/Containerfile`), scripts and docs in the app.
- Unit names don't change. Tell the user to reinstall from `production/` and to remove the stale `podman/frankenphp-octane/` output.

### 4. Update published presets

For each published `development` or `production` preset, compare it with `vendor/foxws/laravel-podman/stubs/{preset}`. **Edit the user's files in place and keep their customizations.**

- **Services** (`pgsql`, `mysql`, `mariadb`, `mongodb`, `valkey`, `redis`, `memcached`, `rustfs`, `meilisearch`, `typesense`, `mailpit`, `reverb`, and any service the user added): add `StopWhenUnneeded={{ondemand}}` under `[Unit]`. For `typesense`, `mailpit` and `reverb`, it replaces `PartOf={{application}}.container`.
- **Health checks:** copy the vendor's `Notify=healthy` + `Health*=` block into `pgsql`, `mysql`, `mariadb`, `mongodb`, `valkey`, `redis`, `rustfs`, `meilisearch`, `typesense` and `mailpit`. Adjust ports if the user changed them.
- **`app.quadlets` `Wants=`:** every installed service besides what the app `Requires=` must be listed, or it stops right after starting. Check which services the user installs (`lpod list`, or the `.quadlets` they published) and add those. In `production`, also add the worker they use (`queue` or `horizon`) and `{{application}}-schedule.timer`.
- **Workers that keep running** (every `production` worker, and `development` workers without `PartOf=` the app): add a `Wants=` line with the services their jobs use (from the app's code: filesystems → `rustfs`, Scout → `typesense`/`meilisearch`, broadcasting → `reverb`, mail → `mailpit`).
- **`systemd/`:** delete `ondemand.socket` and `ondemand.service`. The `ondemand` preset provides them now, along with the idle check. Keep `schedule.timer` in `production`.

### 5. Verify and hand over

Run `php artisan podman:setup` (or `podman:generate` for each preset, including `ondemand`), then check:

- `podman/ondemand/` has `{app}-ondemand.socket`, `{app}-ondemand.service`, `{app}-idle.timer` and `{app}-idle.service`.
- Every service quadlet in `podman/{preset}/` has `StopWhenUnneeded=yes`.
- `php artisan podman:idle` succeeds when nothing is running.

`lpod install` changes the host's systemd units, so **don't run it yourself unless the user asks**. List the commands:

```bash
lpod install ondemand/my-app-ondemand.socket --replace
lpod install ondemand/my-app-idle.timer --replace
lpod install development/app.quadlets --replace
# every other service in use, e.g.:
lpod install development/pgsql.quadlets --replace
```

## From v3 to v4

v4 makes on-demand services the default. The app starts on its first request and stops after `ondemand.idle_timeout` (default `10min`):

- A systemd socket listens on `ondemand.listen` (default `8000`).
- `systemd-socket-proxyd` forwards to the app on `127.0.0.1:{{ondemandPort}}` (default `18000`).

### 1. Ask before choosing a mode

Ask the user whether the app should be on-demand (the default) or always running (`PODMAN_ONDEMAND_ENABLED=false` in `.env`). The stubs are the same either way. Only the app's `StopWhenUnneeded=` and the proxy upstream depend on it.

If `config/podman.php` is published and has no `ondemand` key, add the block from `vendor/foxws/laravel-podman/config/podman.php`.

### 2. Check database and cache majors

v4 pins the images: `postgres:18`, `mysql:26`, `mariadb:13`, `mongo:8`, `valkey:9`, `redis:8`, `memcached:1.6`, `meilisearch:v1.54`, `caddy:2`. These match what `latest` was at release, so auto-updated hosts are already on them. Ask the user to confirm the major their data was written with (e.g. `lpod my-app-pgsql run postgres --version`). If it's different, set that tag in a published preset instead: a database can't open data from a newer major, and moving up a major may need a migration.

### 3. Update published presets

Find the published presets in `config('podman.stubs_path')`, default `containers/stubs/`. Only `development` and `frankenphp-octane` changed. If none are published, skip to step 3.

For each published preset, compare it with `vendor/foxws/laravel-podman/stubs/{preset}`. **Edit the user's files in place and keep their customizations** (volumes, devices, extra services, Horizon instead of queue, `Wants=` entries). Never replace a customized file with the vendor copy.

- **`quadlets/app.quadlets`:**
  - `[Unit]`: add `StopWhenUnneeded={{ondemand}}`.
  - `[Container]`:
    - Remove `PublishPort=8000:8000`, or any publish of the socket's port.
    - Add `PublishPort=127.0.0.1:{{ondemandPort}}:8000`.
    - Replace any `Health*=` lines with the vendor block: `Notify=healthy`, `HealthStartupCmd=curl -fsS -o /dev/null http://127.0.0.1:8000/up`, `HealthStartupInterval=1s`, `HealthStartupTimeout=5s`, `HealthCmd=` (same command), `HealthInterval=1m`, `HealthTimeout=5s`, `HealthRetries=3`.
    - Keep `ExposeHostPort=` lines.
  - `[Build]`: `Environment=UID={{appUid}}`/`Environment=GID={{appGid}}` → `BuildArg=UID={{appUid}}`/`BuildArg=GID={{appGid}}`. `Environment=` is `--env` and never reached the Containerfile's `ARG`s.
- **All other quadlets:** `BindsTo={{application}}.container` → `PartOf={{application}}.container`. Never leave `BindsTo=` on the app: systemd counts it as needing the app, so an on-demand app would never stop.
- **`development` sidecars** (`queue`, `horizon`, `schedule`, `reverb`, `vite`): add `HealthCmd=none` under `[Container]`, as the `frankenphp-octane` ones already have.
- **Database and cache quadlets:** use the tags from step 2.
- **`runtimes/Containerfile`:** `FROM docker.io/dunglas/frankenphp:latest` → `ARG FRANKENPHP_VERSION=1-php8.5` + `FROM docker.io/dunglas/frankenphp:${FRANKENPHP_VERSION}`. Keep a different PHP version if the user had one. The final "Clean up unnecessary files" layer can go. Keep the build-time `key:generate` in `frankenphp-octane`, or restore it if it was removed: the frontend build can boot Laravel (Wayfinder runs `php artisan wayfinder:generate`), which needs a key.
- **`systemd/`:** copy any missing files from the vendor preset: `ondemand.socket`, `ondemand.service`, and `schedule.timer` for `frankenphp-octane`. If the user already has a `systemd/` folder, merge the files in without overwriting theirs.
- **`frankenphp-octane` only:**
  - **Queue worker or Horizon (whichever they use):**
    - Remove the app from `After=`, and remove `BindsTo=`/`PartOf=` to the app.
    - Set `Requires=` and `After=` to the database and cache containers the app itself requires. Copy them from the app's `Requires=`.
    - Append `[Install]` `WantedBy=default.target`.
  - **`schedule.quadlets`:**
    - `schedule:work` → `schedule:run`.
    - Same `Requires=`/`After=` as the worker.
    - Add `Environment=APP_OPTIMIZE=false`.
    - Replace `[Service]` with `Type=oneshot`, `TimeoutStartSec=300` and `TimeoutStopSec=60`. `Restart=` must go: a oneshot can't use `Restart=always`.
  - **`runtimes/entrypoint.sh`:** port the vendor's `chown` loop (only when the directory's owner differs from `PUID:PGID`) and the `APP_OPTIMIZE` check, keeping any directories the user added. Otherwise the scheduler chowns every volume and runs `optimize` each minute.
  - **`app.quadlets`:** remove the worker and the scheduler from `Wants=`.

### 4. Verify the rendered output

```bash
php artisan podman:generate development   # and/or frankenphp-octane, proxy
```

Then check the output in `podman/{preset}/`:

- `app.quadlets` has `StopWhenUnneeded=yes`, or `no` when on-demand is disabled.
- `grep -r BindsTo= podman/` finds nothing.
- `app.quadlets` has `BuildArg=UID=`/`BuildArg=GID=` and no `Environment=UID=`.
- `{app}-ondemand.socket` and `{app}-ondemand.service` exist, plus `{app}-schedule.timer` for `frankenphp-octane`.
- `podman/proxy/runtimes/sites/laravel.Caddyfile` points at `host.containers.internal:{listen port}` when on-demand, or at `systemd-{app}:8000` when not.

Also check that the app has a `GET /up` route (`php artisan route:list --path=up`). Without it, the app never becomes healthy and fails to start.

### 5. Hand the install steps to the user

`lpod install` changes the host's systemd units, so **don't run it yourself unless the user asks**. List the commands for them, using the real app name (the kebab-cased `PODMAN_QUADLET_PREFIX`) and the presets they use:

```bash
lpod my-app down
lpod install development/app.quadlets --replace
lpod install development/my-app-ondemand.socket --replace   # only when on-demand
lpod install proxy/proxy.quadlets --replace
lpod my-app-build restart                                   # rebuild with the new build args
# every other service in use, e.g.:
lpod install development/queue.quadlets --replace
# frankenphp-octane only:
lpod install frankenphp-octane/my-app-schedule.timer --replace
lpod my-app-queue up               # or my-app-horizon
```

Remind them of three things:
- Socket and timer installs need the latest `lpod`. Reinstall it with the curl command in the `lpod` docs.
- On a server, `loginctl enable-linger` is needed once.
- `lpod my-app up` no longer keeps an on-demand app running. A request (`lpod my-app open`) starts it.

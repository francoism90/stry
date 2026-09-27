---
name: podman-upgrade
description: Upgrade foxws/laravel-podman from v3 to v4, where on-demand services (scale-to-zero) became the default. Covers updating published presets in containers/stubs without losing customizations, the new systemd/ folder, BindsTo= to PartOf=, the app health check, the frankenphp-octane worker and scheduler changes, and reinstalling with lpod. Use when upgrading the package, or when published presets still use BindsTo= or lack StopWhenUnneeded={{ondemand}}.
---

# Upgrading laravel-podman to v4

v4 makes on-demand services the default. The app starts on its first request and stops after `ondemand.idle_timeout` (default `10min`):

- A systemd socket listens on `ondemand.listen` (default `8000`).
- `systemd-socket-proxyd` forwards to the app on `127.0.0.1:{{ondemandPort}}` (default `18000`).

The human guide is [UPGRADING.md](https://github.com/foxws/laravel-podman/blob/main/UPGRADING.md). It isn't shipped in `vendor/`, and these notes cover the same steps.

## 1. Ask before choosing a mode

Ask the user whether the app should be on-demand (the default) or always running (`PODMAN_ONDEMAND_ENABLED=false` in `.env`). The stubs are the same either way. Only the app's `StopWhenUnneeded=` and the proxy upstream depend on it.

If `config/podman.php` is published and has no `ondemand` key, add the block from `vendor/foxws/laravel-podman/config/podman.php`.

## 2. Check database and cache majors

v4 pins the images: `postgres:18`, `mysql:26`, `mariadb:13`, `mongo:8`, `valkey:9`, `redis:8`, `memcached:1.6`, `meilisearch:v1.54`, `caddy:2`. These match what `latest` was at release, so auto-updated hosts are already on them. Ask the user to confirm the major their data was written with (e.g. `lpod my-app-pgsql run postgres --version`). If it's different, set that tag in a published preset instead: a database can't open data from a newer major, and moving up a major may need a migration.

## 3. Update published presets

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
- **Queue worker or Horizon, in both presets:** no `BindsTo=`/`PartOf=`/`After=` on the app at all. Use `Requires=`/`After=` on the database and cache the app requires, so jobs keep running while the app is idle (a `PartOf=` worker gets killed after `TimeoutStopSec=`).
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

## 4. Verify the rendered output

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

## 5. Hand the install steps to the user

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

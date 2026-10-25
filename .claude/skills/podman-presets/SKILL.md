---
name: podman-presets
description: Customize foxws/laravel-podman presets and Quadlet templates, such as swapping the database or cache, adding a service, setting memory limits, editing the Caddy proxy or Containerfile, and using placeholders. Use when changing how the app's Podman containers are defined.
---

# Customizing Podman presets

`foxws/laravel-podman` renders preset templates into Podman Quadlet files. Settings live in `config/podman.php`.

## How it fits together

- A preset is a folder with `quadlets/` (`*.quadlets` files) and `runtimes/` (`Containerfile`, `entrypoint.sh`, PHP ini, Caddy files).
- Presets: `development` (working copy mounted), `production` (code baked into the image), `proxy` (Caddy), `devcontainer`, `s3`.
- Your own presets live in `stubs_path` (default `containers/stubs/{preset}`). If one exists there, it replaces the bundled preset completely; files are not merged.
- Rendered output goes to `publish_path` (default `podman/`). **Never edit or commit `podman/`**: it's overwritten on every generate. Edit `containers/stubs/` instead.

## Workflow

```bash
php artisan podman:publish production    # copy templates to containers/stubs/ (once)
# edit containers/stubs/production/...
php artisan podman:generate production   # render to podman/
lpod install production/app.quadlets --replace
lpod my-app restart
```

In the package's devcontainer, run the Artisan steps yourself and give the `lpod` steps to the user to run on the host (see the `podman-lpod` skill). `podman:setup` renders all presets in the `presets` config. `podman:publish --force` overwrites templates you already published, so don't use it without asking.

## Quadlet file format

One `.quadlets` file holds several units. Each starts with a `# FileName=` line, separated by `---`:

```ini
# FileName={{application}}-valkey
[Unit]
Description=Valkey container

[Container]
Image=docker.io/valkey/valkey:9
Volume={{application}}-valkey.volume:/data:rw,Z,U
Network={{application}}.network

[Service]
Restart=always
---
# FileName={{application}}-valkey
[Volume]
VolumeName={{application}}-valkey
```

## Placeholders

| Placeholder | Value |
| --- | --- |
| `{{application}}` | `quadlet_prefix`, kebab-cased (default `APP_NAME`) |
| `{{containerPrefix}}` | `container_prefix` (default `systemd-`), Quadlet's prefix for container and network names |
| `{{proxy}}` | `proxy`, or the `proxy` substitution (`PODMAN_PROXY_PREFIX`) |
| `{{appEnv}}`, `{{appName}}`, `{{appUrl}}` | `app.env`, `app.name`, `app.url` |
| `{{appHost}}` | Host part of `app.url` |
| `{{appUid}}`/`{{appGid}}` | `quadlet_uid`/`quadlet_gid` |
| `{{workingPath}}` | `working_path` (project path on the host) |
| `{{configPath}}` | `config_path` (defaults to `working_path`) |
| `{{runtimePath}}` | The preset's rendered `runtimes/` folder |
| `{{systemctl}}` | `systemctl --user`, or `systemctl` when `quadlet_uid` is `0` (root) |
| `{{appUpstream}}` | Where the proxy sends app traffic: `systemd-{app}:8000`, or the on-demand socket |
| `{{viteHost}}`, `{{reverbHost}}`, `{{s3Host}}`, `{{s3ConsoleHost}}`, `{{mailHost}}` | `vite.`, `ws.`, `s3.`, `fs.`, `mail.` + `{{appHost}}` |
| `{{viteUpstream}}`, `{{reverbUpstream}}`, `{{s3Upstream}}`, `{{s3ConsoleUpstream}}`, `{{mailUpstream}}` | The presets' services: `systemd-{app}-vite:5173`, `-reverb:6001`, `-rustfs:9000`, `-rustfs:9001`, `-mailpit:8025` (web UI) |
| `{{ondemand}}` | `yes` when `ondemand.enabled`, otherwise `no`. Used for `StopWhenUnneeded=` on the app and services |
| `{{ondemandListen}}`, `{{ondemandPort}}`, `{{ondemandIdleTimeout}}`, `{{ondemandIdleWorkers}}` | `ondemand.listen`, `ondemand.port`, `ondemand.idle_timeout`, `ondemand.idle_workers` (space-separated) |

Add your own in `config/podman.php`, usually from `.env` so templates and the app read the same values. Braces are optional, `null` renders as an empty string, and values may contain built-in placeholders (not custom ones). They also override built-in placeholders:

```php
'substitutions' => [
    'apiEndpoint' => env('API_ENDPOINT'),
    'viteUrl' => env('VITE_HMR_URL', 'https://vite.{{appHost}}'),
    'searchUpstream' => '{{containerPrefix}}{{application}}-typesense:8108',
],
```

## Common tasks

### Swap the database or cache

Available per category (default first): database `pgsql`, `mariadb`, `mysql`, `mongodb`; cache `valkey`, `redis`, `memcached`; search `typesense`, `meilisearch`. Use one per category.

The app depends on them explicitly in `app.quadlets`:

```ini
[Unit]
Requires={{application}}-mysql.container {{application}}-redis.container
After={{application}}-mysql.container {{application}}-redis.container
```

Then regenerate, install the new service and `app.quadlets` with `--replace`, and update `.env` (`DB_CONNECTION`, `DB_HOST`, `REDIS_HOST`, ...). Quadlet names containers `systemd-{unit}`, so the host is e.g. `systemd-my-app-mysql`.

Dependency directives:

- `Requires=`: hard dependency. `Wants=`: soft dependency. `After=`: start order only. `PartOf=`: stop or restart with the target.
- Don't use `BindsTo=` on the app. It counts as needing the app, so an on-demand app never stops.
- In `development`, `queue`/`horizon` are `PartOf=` the app and stop when it goes idle. To keep one running, replace its app `After=`/`PartOf=` with `Requires=`/`After=` on the database and cache, as in `production`.

### Add a service

Create `containers/stubs/{preset}/quadlets/my-service.quadlets` in the format above, then run `podman:generate` and `lpod install {preset}/my-service.quadlets`.

To let it sleep with the app:

- Add `StopWhenUnneeded={{ondemand}}` under `[Unit]`, and add the service to the app's `Wants=` line. Without `Wants=`, it stops right after starting.
- If the first request after waking needs it (storage, search), also add it to the app's `After=` line. `Wants=` alone doesn't make the app wait.
- If queue workers keep running while the app sleeps (always in `production`) and their jobs use it, add it to the workers' `Wants=` and `After=` too.
- If it can have work in progress while the app sleeps, write an `IdleCheck` for it (`name()`, `isEnabled()`, `run()`) and add it to the `idle.checks` config list or `app(PodmanIdle::class)->checks([...])`. `podman:idle` then runs it; `podman:idle --services=its-name` runs it alone.

### Memory limit

Add `Memory=1G` under `[Container]` in that service's `.quadlets` file, then regenerate and reinstall it.

### Proxy (Caddy)

Publish `proxy`, then edit `containers/stubs/proxy/runtimes/Caddyfile` and `sites/*.Caddyfile`. The default site routes `APP_URL` to the app and `vite.`, `ws.` (Reverb), `s3.` and `fs.` (RustFS) and `mail.` (Mailpit) to their services, all through placeholders (`{{viteHost}}`/`{{viteUpstream}}` and so on). Change a host or upstream in `substitutions` without publishing, e.g. `'viteHost' => env('VITE_HMR_HOST', 'vite.{{appHost}}')`; keep a default, since an empty host stops Caddy. Publish to drop or add sites. Regenerate and run `lpod proxy restart`.

To serve Reverb or Mailpit under a path of the app's domain (`/ws`, `/mail`) instead of a subdomain, build `CADDY_SERVER_EXTRA_DIRECTIVES` in `config/octane.php` with `PodmanCaddyRoutes::make()->route($path, $host, $port)` (Reverb, stripped path, plus Echo's `wsPath`) and `->route(..., stripPrefix: false)` (Mailpit, with `MP_WEBROOT` set to the same path). Keep S3, the RustFS console and Vite on subdomains: signed S3 URLs and Vite's modules break under a path. See docs/customizing.md "Paths instead of subdomains".

### On-demand (scale-to-zero)

On by default. `PODMAN_ONDEMAND_ENABLED=false` renders `StopWhenUnneeded=no` everywhere and points the proxy straight at the container.

- **Socket:** the `ondemand` preset (shared by `development` and `production`) holds the socket and its `systemd-socket-proxyd` service. Files in a preset's `systemd/` folder are plain systemd units, rendered as `{application}-{file}`; `production` also has `systemd/schedule.timer`.
- **Idle check:** the `ondemand` preset's `laravel-idle@.timer`/`laravel-idle@.service` templates, shared by every app on the host (`%i` is the app), plus the app's drop-in `laravel-idle@{{application}}.service.d/idle.conf`. Files with `@.` in their name keep it when rendered, and `.d` folders render as drop-ins with placeholders in their name. The socket `Wants=laravel-idle@{{application}}.timer`, and the timer is `PartOf=%i-ondemand.socket`, so installing the socket turns it on, with or without lpod. While the app sleeps, it runs `podman:idle` in each running worker of `ondemand.idle_workers` (`PODMAN_ONDEMAND_IDLE_WORKERS`, default `queue,horizon`), and once nothing is in progress, stops the workers and the scheduler timer. To check another worker, add it to `idle_workers` rather than editing the template: the drop-in sets `LARAVEL_IDLE_WORKERS` from it.
- **Sleeping services:** a service with `StopWhenUnneeded={{ondemand}}` stops once no running unit needs it, so it must be in the app's `Requires=` or `Wants=`, or it stops right after starting.
- **App quadlet:** when editing it, keep `StopWhenUnneeded={{ondemand}}`, `Notify=healthy` with the `/up` health check, and the `127.0.0.1:{{ondemandPort}}` publish.

```bash
lpod install ondemand/{application}-ondemand.socket --replace
```

### Extra PHP extensions or packages

Edit `containers/stubs/{preset}/runtimes/Containerfile`, or pass the `PHP_EXTENSIONS` build arg.

## Config keys

`PODMAN_QUADLET_PREFIX`, `PODMAN_CONTAINER_PREFIX`, `PODMAN_PROXY_PREFIX`, `PODMAN_STUBS_PATH`, `PODMAN_WORKING_PATH` (override once with `podman:generate --working-path=`), `PODMAN_CONFIG_PATH`, `PODMAN_QUADLET_UID`/`_GID`, `PODMAN_PUBLISH_PATH`, `PODMAN_SELINUX_VOLUME_MAPPING` (turn off on hosts without SELinux), `PODMAN_DEFAULT_PRESETS`, `PODMAN_ONDEMAND_ENABLED`/`_LISTEN`/`_PORT`/`_IDLE_TIMEOUT`.

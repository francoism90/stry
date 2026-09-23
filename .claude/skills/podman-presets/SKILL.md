---
name: podman-presets
description: Customize foxws/laravel-podman presets and Quadlet templates, such as swapping the database or cache, adding a service, setting memory limits, editing the Caddy proxy or Containerfile, and using placeholders. Use when changing how the app's Podman containers are defined.
---

# Customizing Podman presets

`foxws/laravel-podman` renders preset templates into Podman Quadlet files. Settings live in `config/podman.php`.

## How it fits together

- A preset is a folder with `quadlets/` (`*.quadlets` files) and `runtimes/` (`Containerfile`, `entrypoint.sh`, PHP ini, Caddy files).
- Presets: `development` (working copy mounted), `frankenphp-octane` (code baked into the image), `proxy` (Caddy), `devcontainer`, `s3`.
- Your own presets live in `stubs_path` (default `containers/stubs/{preset}`). If one exists there, it replaces the bundled preset completely; files are not merged.
- Rendered output goes to `publish_path` (default `podman/`). **Never edit or commit `podman/`**: it's overwritten on every generate. Edit `containers/stubs/` instead.

## Workflow

```bash
php artisan podman:publish frankenphp-octane    # copy templates to containers/stubs/ (once)
# edit containers/stubs/frankenphp-octane/...
php artisan podman:generate frankenphp-octane   # render to podman/
lpod install frankenphp-octane/app.quadlets --replace
lpod my-app restart
```

`podman:setup` renders all presets in the `presets` config. `podman:publish --force` overwrites templates you already published, so don't use it without asking.

## Quadlet file format

One `.quadlets` file holds several units. Each starts with a `# FileName=` line, separated by `---`:

```ini
# FileName={{application}}-valkey
[Unit]
Description=Valkey container

[Container]
Image=docker.io/valkey/valkey:latest
Volume={{application}}-valkey.volume:/data:rw,Z,U
Network={{application}}.network

[Service]
Restart=always
---
# FileName={{application}}-valkey
[Volume]
VolumeName=systemd-{{application}}-valkey
```

## Placeholders

| Placeholder | Value |
| --- | --- |
| `{{application}}` | `quadlet_prefix`, kebab-cased (default `APP_NAME`) |
| `{{proxy}}` | `proxy_prefix`, kebab-cased |
| `{{appEnv}}`, `{{appName}}`, `{{appUrl}}` | `app.env`, `app.name`, `app.url` |
| `{{appHost}}` | Host part of `app.url` |
| `{{appUid}}`/`{{appGid}}` | `quadlet_uid`/`quadlet_gid` |
| `{{workingPath}}` | `working_path` (project path on the host) |
| `{{configPath}}` | `config_path` (defaults to `working_path`) |
| `{{runtimePath}}` | The preset's rendered `runtimes/` folder |

Add your own in `config/podman.php`. They also override built-in placeholders:

```php
'substitutions' => [
    '{{apiEndpoint}}' => env('API_ENDPOINT'),
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

Dependency directives: `Requires=` (hard), `Wants=` (soft), `After=` (order only), `BindsTo=` (stop with target), `PartOf=` (stop/restart with target).

### Add a service

Create `containers/stubs/{preset}/quadlets/my-service.quadlets` in the format above, then `podman:generate` and `lpod install {preset}/my-service.quadlets`.

### Memory limit

Add `Memory=1G` under `[Container]` in that service's `.quadlets` file, then regenerate and reinstall it.

### Proxy (Caddy)

Publish `proxy`, then edit `containers/stubs/proxy/runtimes/Caddyfile` and `sites/*.Caddyfile`. The default site routes `APP_URL` to the app, and the subdomains `vite.`, `ws.` (Reverb), `s3.` and `fs.` (RustFS) and `mail.` (Mailpit). Regenerate and run `lpod proxy restart`.

### Extra PHP extensions or packages

Edit `containers/stubs/{preset}/runtimes/Containerfile`, or pass the `PHP_EXTENSIONS` build arg.

## Config keys

`PODMAN_QUADLET_PREFIX`, `PODMAN_PROXY_PREFIX`, `PODMAN_STUBS_PATH`, `PODMAN_WORKING_PATH` (override once with `podman:generate --working-path=`), `PODMAN_CONFIG_PATH`, `PODMAN_QUADLET_UID`/`_GID`, `PODMAN_PUBLISH_PATH`, `PODMAN_SELINUX_VOLUME_MAPPING` (turn off on hosts without SELinux), `PODMAN_DEFAULT_PRESETS`.

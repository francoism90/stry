---
title: Upgrading
section: Getting Started
order: 5
tags:
    - upgrade
    - production
    - migrations
---

# Upgrading

How to upgrade a running production install to a new **stry** release. For a first install, see [Production Setup](production.md).

## Before you start

- Check the [GitHub releases](https://github.com/francoism90/stry/releases) for breaking changes since your current version. Upgrading from 1.x to 2.0 needs extra steps: follow the [upgrade guide](https://github.com/francoism90/stry/blob/main/UPGRADE.md).
- Back up the database. See the [backup example](production.md#security-checklist) in Production Setup.
- Images are tagged `latest` (the newest stable release), `{major}`, `{major}.{minor}` and the full version number. The Quadlet files use the major tag, such as `ghcr.io/francoism90/stry:3`, so updates stay within the release line you're on. Set `PODMAN_IMAGE_REGISTRY` in `.env` to pin another tag.

## Pull the new image

```bash
podman pull ghcr.io/francoism90/stry:3
lpod install production/app.quadlets --replace
# ...repeat for every other service whose image changed, such as stry-horizon...
```

`--replace` updates the Quadlet unit and restarts the service. It leaves existing secrets and volumes alone.

Instead of pulling and reinstalling each service by hand, you can let Podman do it. Every service in the `production` preset has `AutoUpdate=registry` set, so this updates all of them:

```bash
systemctl --user restart podman-auto-update
```

Podman pulls a new image for each of these services and restarts only the ones that changed. It stays on the tag in the Quadlet file, so `stry:3` gets 3.x releases but never moves to 4.0 by itself: a new major comes with an upgrade guide, and its Quadlet files use the new tag. This runs once. To run it on a schedule, enable `podman-auto-update.timer`.

Auto-update only pulls images and restarts containers. It doesn't run Artisan commands, so you still need the migration and cache steps below.

## Regenerate the Podman files, if needed

You only need this when the release notes mention changes to `containers/stubs/*`. If they do, repeat the option you used during setup (see [Generate the Podman files](production.md#generate-the-podman-files)), then reinstall the changed units with `lpod install ... --replace`.

### Media folders moved to `/srv/library`

The media folders used to be fixed at `/mnt/media/videos`, `/mnt/media/import` and `/mnt/media/exports`. They're now set with `PODMAN_VIDEOS_PATH`, `PODMAN_IMPORT_PATH` and `PODMAN_EXPORTS_PATH`, and default to `/srv/library/videos`, `/srv/library/import` and `/srv/library/exports`. To keep your library where it is, add these to your `.env` **before** you regenerate the Podman files:

```ini
PODMAN_VIDEOS_PATH=/mnt/media/videos
PODMAN_IMPORT_PATH=/mnt/media/import
PODMAN_EXPORTS_PATH=/mnt/media/exports
```

Without them, the regenerated `stry`, `stry-horizon` and `stry-schedule` units mount the empty `/srv/library` folders, and your videos can't be found until you set them and reinstall the units. Nothing is deleted: the files stay in `/mnt/media`.

### Removing `U` and `z` from the app's mounts

Older `production` templates mounted the app's volumes with Podman's `U` option, and every mount with `z`. `U` makes `/cache` root-owned every time the app starts, which breaks streaming after the app wakes up. `z` walks your whole library and `/cache` on every container start, and the scheduler starts one every minute. Named volumes don't need `z`: Podman's SELinux policy already labels them. If your installed `stry`, `stry-horizon` or `stry-schedule` units still have them:

1. On SELinux hosts, label the media folders once (see [Storage, ownership & SELinux](podman.md#storage-ownership--selinux)):

    ```bash
    sudo semanage fcontext -a -t container_file_t '/mnt/media(/.*)?'
    sudo restorecon -R /mnt/media
    ```

2. Regenerate the files and reinstall the three units:

    ```bash
    php artisan podman:generate production
    lpod install production/app.quadlets --replace
    lpod install production/horizon.quadlets --replace
    lpod install production/schedule.quadlets --replace
    ```

If you installed the units by hand, remove `,U` from every `Volume=` line in `stry.container`, and `,z` from the `/media`, `/import`, `/config`, `/data` and `/cache` lines in all three units. Then run `systemctl --user daemon-reload` and restart them.

### The idle check comes with the on-demand socket

The idle check is part of the on-demand socket now: installing `stry-ondemand.socket` also installs and starts `stry-idle.timer`. `lpod idle enable` isn't needed anymore. Upgrade `lpod` to 3.0 or later by running its installer again, regenerate the `ondemand` preset, then reinstall the socket and turn off the old check:

```bash
curl -fsSL https://github.com/foxws/lpod/releases/latest/download/install.sh | bash
php artisan podman:generate ondemand
lpod install ondemand/stry-ondemand.socket --replace
lpod idle disable stry                       # turns off the old lpod-idle@stry.timer
systemctl --user disable stry-idle.timer     # only if you enabled it by hand before
```

On hosts set up with the [installer](install.md), which don't use `lpod`, the old `stry-idle.timer` keeps working until you copy the new units over. To switch, copy `podman/ondemand/stry-ondemand.*` and `podman/ondemand/stry-idle.*` to `~/.config/systemd/user`, then run `systemctl --user daemon-reload` and `systemctl --user restart stry-ondemand.socket`.

### The proxy's volumes lost their `systemd-` prefix

The `proxy` preset now names its volumes `proxy-config` and `proxy-data` instead of `systemd-proxy-config` and `systemd-proxy-data`. Caddy keeps its certificates in the data volume. If you use the proxy, copy both volumes over before you reinstall it, or Caddy starts empty and requests new certificates:

```bash
systemctl --user stop proxy
for old in systemd-proxy-config systemd-proxy-data; do
    new="${old#systemd-}"
    podman volume create "$new"
    podman volume export "$old" | podman volume import "$new" -
done
lpod install proxy/proxy.quadlets --replace
```

Once the proxy works, remove the old volumes with `podman volume rm`. stry's own volumes, such as `stry-pgsql`, already had these names, and container names such as `systemd-stry-pgsql` don't change, so `DB_HOST` and the other hosts in `.env` stay the same.

:::note
If you installed the Quadlet units by hand from the templates instead of running `podman:setup`, you need to compare and update them yourself on every upgrade (see the note in [Production Setup](production.md#generate-the-podman-files)).
:::

## Run migrations

```bash
lpod stry artisan app:update
```

This runs both the database migrations and the [settings migrations](configuration.md#shipping-new-defaults) in `database/settings/*`, clears the settings cache, and restarts the Octane and queue workers. Settings migrations update values that admins can edit, such as `ChapterSettings::$patterns`, on existing installs.

Until the migrations have run, admins see a notice at the top of every page.

To run this every time the app container starts, add `APP_AUTO_UPDATE=true` to your `.env`. Only the `stry` container runs it, before the web server starts.

## Re-sync search indexes, if needed

You only need this when a release adds a new searchable model or field:

```bash
lpod stry artisan scout:sync --import
```

Or run `lpod stry artisan app:update --scout` instead of the migration step above.

See [CLI Interaction](interaction.md#search) for all Scout commands.

## Check that it worked

```bash
systemctl --user status stry
curl -I https://your-domain/
journalctl --user -u 'stry*' -f
```

## Rolling back

Not every migration can be undone. Settings migrations, for example, have no `down()` method at all (see [Application Configuration](configuration.md#shipping-new-defaults)). If an upgrade goes wrong:

1. Restore the database backup you made before upgrading.
2. Go back to the previous image: `podman pull ghcr.io/francoism90/stry:<previous-tag>`, then `lpod install production/app.quadlets --replace`.

Don't rely on `migrate:rollback` to undo a release. Your database backup is the real way back.

## See also

- [Production Setup](production.md): first install
- [Application Configuration](configuration.md#admin-managed-settings): settings migrations and cache
- [CLI Interaction](interaction.md): `lpod` and Artisan commands
- [Podman Quadlet](podman.md): service names, installing and secrets

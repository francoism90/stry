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

- Check the [GitHub releases](https://github.com/francoism90/stry/releases) for breaking changes since your current version, and follow the [upgrade guide](https://github.com/francoism90/stry/blob/main/UPGRADE.md) when a release has one. Installs that still have a `podman/` folder or `PODMAN_*` keys in their `.env` need its "Moving to plain Quadlet units" steps once.
- Back up the database. See the [backup example](production.md#security-checklist) in Production Setup.
- Images are tagged `latest` (the newest stable release), `{major}`, `{major}.{minor}` and the full version number. The Quadlet files use the major tag, such as `ghcr.io/francoism90/stry:4`, so updates stay within the release line you're on. To pin another tag, set `Image=` in a drop-in for each app unit (see [Changing a unit](podman.md#changing-a-unit)).

## Pull the new image

Every service in `containers/quadlets/production/` has `AutoUpdate=registry` set, so this pulls the new images and restarts only the services that changed:

```bash
systemctl --user start podman-auto-update
```

It stays on the tag in the Quadlet file, so `stry:4` gets 4.x releases but never moves to 5.0 by itself: a new major comes with an upgrade guide, and its Quadlet files use the new tag. The other services, such as PostgreSQL, also stay on the tags in your installed Quadlet files until you update the units. To run it on a schedule, enable `podman-auto-update.timer`. In [Pods](https://github.com/foxws/pods), use **Redeploy** on the application.

The image of the latest stable release is also rebuilt every week with the latest Debian and PHP security updates, under the same tags, so auto-update picks those up between releases too.

Auto-update only pulls images and restarts containers. It doesn't run Artisan commands, so you still need the migration and cache steps below.

## Update the units, if needed

You only need this when the release notes mention changes to `containers/quadlets/production/`. Reinstall the units from the new release; your secrets, volumes and drop-ins stay as they are:

```bash
cd ~/projects/stry && git pull
podman quadlet install --replace --application=stry containers/quadlets/production
systemctl --user restart stry
```

With Pods, choose the new release under **Update** on the application page. It keeps your drop-ins, and then offers **Update stry** (`after_update` in `pods.yml`), which runs the migration step below.

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
2. Go back to the previous image: pin `Image=ghcr.io/francoism90/stry:<previous-tag>` in a drop-in for each app unit, then run `systemctl --user daemon-reload` and restart `stry`.

Don't rely on `migrate:rollback` to undo a release. Your database backup is the real way back.

## Upgrading PostgreSQL to a new major version

`AutoUpdate=registry` keeps `stry-pgsql` on the tag in its Quadlet file, such as `postgres:18`, so it never moves to a new major version by itself. A new major can't read the old data files. Changing the tag means you dump the database, start the new version on an empty data folder, and restore the dump. The steps below go from 18 to 19, so swap in your own versions.

1. Stop the app and everything that can start it again. Skip any units you don't have:

    ```bash
    systemctl --user stop stry-ondemand.socket stry-idle.timer stry-schedule.timer stry stry-horizon stry-reverb
    ```

2. Dump the database while the old version is still running:

    ```bash
    podman exec systemd-stry-pgsql sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Fc' > ~/stry-pgsql.dump
    podman run --rm -i --entrypoint pg_restore docker.io/library/postgres:19 -l < ~/stry-pgsql.dump | head
    ```

    The second command reads the dump with the new version's `pg_restore`, so you know it's complete and readable before you go on.

3. Stop PostgreSQL and move the old data out of the volume. The images keep each major version in its own folder, such as `18/docker`. The old image also left a `data -> .` symlink in the volume. Remove it too: the new image takes it for an old data folder and won't start.

    ```bash
    systemctl --user stop stry-pgsql
    V=$(podman volume inspect stry-pgsql --format '{{.Mountpoint}}')
    podman unshare sh -c "mkdir -p ~/stry-pgsql-old && mv $V/18 ~/stry-pgsql-old/ && rm -f $V/data"
    ```

4. Change `Image=` in `podman/production/pgsql.quadlets` to the new tag, then reinstall the unit:

    ```bash
    lpod install production/pgsql.quadlets --replace
    systemctl --user reset-failed stry-pgsql
    ```

5. With the on-demand socket, PostgreSQL stops as soon as nothing needs it. Keep it running with a temporary unit, then restore the dump and rebuild the planner statistics:

    ```bash
    systemd-run --user --unit=stry-pgsql-hold -p Requires=stry-pgsql.service -p After=stry-pgsql.service sleep infinity
    podman exec systemd-stry-pgsql sh -c 'psql -U "$POSTGRES_USER" -d postgres -tAc "SHOW server_version"'
    podman exec -i systemd-stry-pgsql sh -c 'pg_restore -U "$POSTGRES_USER" -d "$POSTGRES_DB" --clean --if-exists --exit-on-error --single-transaction' < ~/stry-pgsql.dump
    podman exec systemd-stry-pgsql sh -c 'vacuumdb -U "$POSTGRES_USER" -d "$POSTGRES_DB" --analyze-in-stages'
    systemctl --user stop stry-pgsql-hold
    ```

    `--single-transaction` makes the restore all or nothing. If it fails, the database stays empty and you can run it again.

6. Start the app again with the units you stopped in step 1, and [check that it worked](#check-that-it-worked).

Once the app works, delete `~/stry-pgsql-old`. Until then, you can go back by moving the old folder into the volume and switching the tag back.

## See also

- [Production Setup](production.md): first install
- [Application Configuration](configuration.md#admin-managed-settings): settings migrations and cache
- [CLI Interaction](interaction.md): `lpod` and Artisan commands
- [Podman Quadlet](podman.md): service names, installing and secrets

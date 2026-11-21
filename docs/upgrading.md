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
- Images are tagged `latest` (the newest stable release), `{major}`, `{major}.{minor}` and the full version number. The Quadlet files use `latest`. To stay on one release line, pin the major tag instead, such as `ghcr.io/francoism90/stry:4`, with `Image=` in a drop-in for each app unit (see [Changing a unit](podman.md#changing-a-unit)).

## Pull the new image

Every service in `containers/quadlets/production/` has `AutoUpdate=registry` set, so this pulls the new images and restarts only the services that changed:

```bash
systemctl --user start podman-auto-update
```

With `latest`, this also moves to a new major release as soon as it's out. A new major comes with an upgrade guide, so read it before you pull, or pin the major tag (see above) and move on when you're ready. The other services, such as PostgreSQL, stay on the tags in your installed Quadlet files until you update the units. To run it on a schedule, enable `podman-auto-update.timer`. In [Pods](https://github.com/foxws/pods), use **Redeploy** on the application.

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

## See also

- [Production Setup](production.md): first install
- [Application Configuration](configuration.md#admin-managed-settings): settings migrations and cache
- [CLI Interaction](interaction.md): `lpod` and Artisan commands
- [Podman Quadlet](podman.md): service names, installing and secrets

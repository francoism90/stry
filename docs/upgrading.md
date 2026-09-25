---
title: Upgrading
sidebar_position: 3
tags:
    - upgrade
    - production
    - migrations
---

# Upgrading

How to upgrade a running production install to a new **stry** release. For a first install, see [Production Setup](production.md).

## Before you start

- Check the [GitHub releases](https://github.com/francoism90/stry/releases) for breaking changes since your current version.
- Back up the database. See the [backup example](production.md#security-checklist) in Production Setup.
- Images are tagged `latest` (the newest stable release), `{major}.{minor}` and the full version number. Use a specific tag instead of `latest` if you want to decide when to upgrade.

## Pull the new image

```bash
podman pull ghcr.io/francoism90/stry:latest
lpod install frankenphp-octane/app.quadlets --replace
# ...repeat for every other service whose image changed, such as stry-horizon...
```

`--replace` updates the Quadlet unit and restarts the service. It leaves existing secrets and volumes alone.

Instead of pulling and reinstalling each service by hand, you can let Podman do it. Every service in the `frankenphp-octane` preset has `AutoUpdate=registry` set, so this updates all of them:

```bash
systemctl --user restart podman-auto-update
```

Podman pulls a new image for each of these services and restarts only the ones that changed. This runs once. To run it on a schedule, enable `podman-auto-update.timer`.

Auto-update only pulls images and restarts containers. It doesn't run Artisan commands, so you still need the migration and cache steps below.

## Regenerate the Podman files, if needed

You only need this when the release notes mention changes to `containers/stubs/*`. If they do, repeat the option you used during setup (see [Generate the Podman files](production.md#generate-the-podman-files)), then reinstall the changed units with `lpod install ... --replace`.

:::note
If you installed the Quadlet units by hand from the templates instead of running `podman:setup`, you need to compare and update them yourself on every upgrade (see the note in [Production Setup](production.md#generate-the-podman-files)).
:::

## Run migrations

```bash
lpod stry artisan migrate --force
```

This runs both the database migrations and the [settings migrations](configuration.md#shipping-new-defaults) in `database/settings/*`. Settings migrations update values that admins can edit, such as `ChapterSettings::$patterns`, on existing installs.

If `SETTINGS_CACHE_ENABLED=true` (the default), clear the settings cache afterwards:

```bash
lpod stry artisan settings:clear-cache
```

## Re-sync search indexes, if needed

You only need this when a release adds a new searchable model or field:

```bash
lpod stry artisan scout:sync --import
```

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
2. Go back to the previous image: `podman pull ghcr.io/francoism90/stry:<previous-tag>`, then `lpod install frankenphp-octane/app.quadlets --replace`.

Don't rely on `migrate:rollback` to undo a release. Your database backup is the real way back.

## See also

- [Production Setup](production.md): first install
- [Application Configuration](configuration.md#admin-managed-settings): settings migrations and cache
- [CLI Interaction](interaction.md): `lpod` and Artisan commands
- [Podman Quadlet](podman.md): service names, installing and secrets

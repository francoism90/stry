---
title: Upgrading
sidebar_position: 3
tags:
    - upgrade
    - production
    - migrations
---

# Upgrading

Steps to upgrade an already-running production instance to a new **stry** release. For first-time setup, see [Production Setup](production.md).

## Before you start

- Check the [GitHub Releases](https://github.com/francoism90/stry/releases) page for breaking changes since your current version.
- Back up the database — see the [backup cron example](production.md#security-checklist) in Production Setup.
- Images are tagged `latest` (stable releases), `{major}.{minor}`, and full semver (`{version}`) — pin to a specific tag instead of `latest` if you want to control exactly when you upgrade.

## Pull the new image

```bash
podman pull ghcr.io/francoism90/stry:latest
lpod install frankenphp-octane/app.quadlets --replace
# ...repeat for any other service whose image changed (e.g. stry-horizon)...
```

`--replace` updates the Quadlet unit and restarts the service — it doesn't touch existing secrets or volumes.

Every service unit in the `frankenphp-octane` preset carries `AutoUpdate=registry`, so instead of pulling and reinstalling each service by hand you can trigger Podman's own auto-update run:

```bash
systemctl --user restart podman-auto-update
```

This pulls a fresh image for every labeled unit and restarts only the ones that actually changed. It's a one-shot run, not a background watcher — enable `podman-auto-update.timer` if you want it to happen on a schedule. Either way, still run the migrations and cache-clear steps below afterward — auto-update only pulls images and restarts containers, it doesn't run Artisan commands.

## Regenerate the Podman files, if needed

Skip this unless a release note mentions changes to `containers/stubs/*`. If it does, re-run whichever option you used during setup (see [Generate the Podman files](production.md#generate-the-podman-files)) and reinstall only the units that changed with `lpod install ... --replace`.

:::note
If you installed Quadlet units by hand from the raw templates instead of running `podman:setup`, you're responsible for diffing and updating them yourself on every upgrade — that's the trade-off for skipping it (see the note in [Production Setup](production.md#generate-the-podman-files)).
:::

## Run migrations

```bash
lpod stry artisan migrate --force
```

This runs both schema migrations and [settings migrations](configuration.md#shipping-new-defaults) (`database/settings/*`) — the latter update admin-editable values (e.g. `ChapterSettings::$patterns`) for installs that already exist. If `SETTINGS_CACHE_ENABLED=true` (the default), also clear the settings cache afterward:

```bash
lpod stry artisan settings:clear-cache
```

## Re-sync search indexes, if needed

Only needed if a release adds a new searchable model or field:

```bash
lpod stry artisan scout:sync --import
```

See [CLI Interaction](interaction.md#search) for the full set of Scout commands.

## Check it worked

```bash
systemctl --user status stry
curl -I https://your-domain/
journalctl --user -u 'stry*' -f
```

## Rolling back

Migrations in this app aren't guaranteed to be reversible — some, like settings migrations, don't define a `down()` at all (see [Application Configuration](configuration.md#shipping-new-defaults)). If an upgrade goes wrong:

1. Restore the database backup you took before upgrading.
2. Pull and reinstall the previous image tag: `podman pull ghcr.io/francoism90/stry:<previous-tag>`, then `lpod install frankenphp-octane/app.quadlets --replace`.

Don't rely on `migrate:rollback` alone to undo a release — treat the database backup as the real rollback path.

## See also

- [Production Setup](production.md) — first-time install
- [Application Configuration](configuration.md#admin-managed-settings) — settings migrations and cache
- [CLI Interaction](interaction.md) — `lpod` and Artisan commands
- [Podman Quadlet](podman.md) — service names and the install/secrets flow

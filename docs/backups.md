---
title: Database Snapshots
section: Configuration
order: 6
tags:
    - backups
    - snapshots
    - database
---

# Database Snapshots

**stry** can dump its PostgreSQL database to a disk on a schedule. A snapshot holds your users, profiles, videos, tags, chapters and settings, so you can bring an install back after a failed upgrade or a lost database volume. It uses [spatie/laravel-db-snapshots](https://github.com/spatie/laravel-db-snapshots).

:::warning
A snapshot only contains the database, not the media files. Back up the folders or buckets of your disks too, following the [3-2-1](https://www.backblaze.com/blog/the-3-2-1-backup-strategy/) backup plan. A snapshot without the files it refers to can't play anything.
:::

## Schedule snapshots

Open **Admin → Snapshots**:

| Setting           | What it does                                                                                     |
| ----------------- | ------------------------------------------------------------------------------------------------ |
| Frequency         | `Never` (the default), `Daily`, `Weekly` or `Monthly`                                            |
| Snapshots to keep | How many snapshots are kept (1–100, default 7). Older ones are deleted after a new one is made   |
| Compress          | Gzip the snapshots (on by default). They get much smaller, but take longer to create and restore |

Snapshots are created at 04:30 UTC, after the other nightly maintenance tasks. Weekly snapshots run on Mondays and monthly ones on the first of the month.

The `schedule` service creates them, so it must be running. A changed frequency applies from the next minute, without a restart.

:::note
With the [on-demand socket](podman.md), the scheduler timer stops while the app is asleep, so no snapshot is made then. Run `app:snapshot` yourself, for example from a host cron job or a systemd timer, if the app is often idle at night.
:::

## Where snapshots are stored

Snapshots go to the disk that's the default for **Snapshots** under **Admin → Storage**. When you migrate, stry adds a `snapshots` disk in the `snapshots` folder of your exports disk, `/exports/snapshots` on a default install. An S3 exports disk passes on its bucket and credentials.

You can point the disk somewhere else, or make another disk the default for snapshots, like any other disk (see [Storage](configuration.md#storage)). Storing them away from the database volume, on another drive or in a bucket, means losing one doesn't lose the other.

Each snapshot is named after the time it was made, for example `2026-10-27_04-30-00.sql.gz`.

## Create a snapshot now

```bash
lpod stry artisan app:snapshot
```

This uses the same disk, compression and number to keep as the scheduled snapshots, so it can also delete the oldest one. Take one before upgrading.

## Restore a snapshot

:::danger
Loading a snapshot drops every table in the database first, then loads the snapshot. Everything changed after the snapshot was made is lost.
:::

```bash
# List the snapshots, newest first
lpod stry artisan snapshot:list

# Load one by name, or the newest with --latest. --force skips the confirmation in production
lpod stry artisan snapshot:load 2026-10-27_04-30-00 --force
lpod stry artisan snapshot:load --latest --force

# Then migrate, in case the snapshot is from an older release, and restart the workers
lpod stry artisan app:update
```

The `snapshot:*` commands read the disk named `snapshots` from `config/db-snapshots.php`. If you made a disk with another name the default for snapshots, set that name in the config before restoring.

## See also

- [Application Configuration](configuration.md#storage): managing the storage disks
- [CLI Interaction](interaction.md#strys-artisan-commands): stry's Artisan commands
- [Upgrading](upgrading.md): updating an existing install

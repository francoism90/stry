---
title: Interaction
order: 6
tags:
    - shell
    - bash
    - commands
---

# CLI Interaction

**stry** uses [`lpod`](https://github.com/foxws/lpod), a Laravel Sail-style CLI paired with `foxws/laravel-podman`, for day-to-day container interaction.

```bash
lpod stry up                     # start
lpod stry shell                  # shell in (alias: bash)
lpod stry tinker                 # Laravel Tinker
lpod stry artisan migrate        # or: lpod stry a migrate
```

> [!TIP]
> `lpod` is a standalone tool, installed once per host — see [Podman Quadlet](podman.md#prerequisites) for the install command.

## stry's Artisan commands

Run any of these via `lpod stry artisan ...` (or the `a` shorthand):

### Users

| Command                     | Description                          |
| ---------------------------- | ------------------------------------- |
| `users:create`                | Create a user account (interactive)   |
| `users:create --admin`        | ...and assign the admin role          |
| `users:create --super-admin`  | ...and assign the super-admin role    |

### Videos

| Command           | Description                                 |
| ------------------ | -------------------------------------------- |
| `videos:import`     | Import videos for a user                     |
| `videos:clear`      | Remove soft-deleted videos from filesystem   |

### Tags

| Command       | Description                       |
| -------------- | ----------------------------------- |
| `tags:create`   | Create a new tag                    |
| `tags:sort`     | Sort tags alphabetically by type    |

### Playlists & media

| Command                                                    | Description                                     |
| ------------------------------------------------------------ | -------------------------------------------------- |
| `playlists:clear`                                             | Remove generated DASH playlists from filesystem     |
| `transcodes:clear`                                            | Force delete failed transcodes                      |
| `transcodes:clear --all`                                      | Force delete all expired transcodes                 |
| `groups:clear`                                                 | Detach all videos from groups of a given type        |
| `media-library:regenerate --only-missing --queue-all`          | Regenerate missing media conversions                 |

### Search

| Command                          | Description                                                            |
| ---------------------------------- | -------------------------------------------------------------------------- |
| `scout:sync`                        | Sync Typesense indexes (configure collections)                             |
| `scout:sync --import`               | ...and import all model records                                            |
| `scout:sync --delete`               | Delete all indexes and re-sync (clears existing data)                      |
| `scout:delete-index Model`          | Delete a specific index (useful for corrupted indexes)                     |
| `scout:import Model`                | Import a specific model into its search index                             |

## Direct Podman access

```bash
podman exec -it systemd-stry php artisan help
podman exec -it systemd-stry-horizon /bin/bash
```

## See also

- [Podman Quadlet](podman.md) — service names and the install/secrets flow
- [Application Configuration](configuration.md) — app settings
- [Object Storage (S3)](s3.md) — media file management

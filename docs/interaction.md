---
title: Interaction
sidebar_position: 9
tags:
    - shell
    - bash
    - commands
---

# CLI Interaction

**stry** uses [`lpod`](https://github.com/foxws/lpod) to work with its containers, much like Laravel Sail but for Podman.

```bash
lpod stry up                     # start
lpod stry shell                  # open a shell (alias: bash)
lpod stry tinker                 # Laravel Tinker
lpod stry artisan migrate        # or: lpod stry a migrate
```

:::tip
`lpod` is a separate tool that you install once per host. See [Podman Quadlet](podman.md#prerequisites) for how to install it.
:::

## stry's Artisan commands

Run these with `lpod stry artisan ...`, or the shorter `lpod stry a ...`.

### Users

| Command                      | Description                                      |
| ---------------------------- | ------------------------------------------------ |
| `users:create`               | Create a user account (asks for details)         |
| `users:create --admin`       | The same, and give the user the admin role       |
| `users:create --super-admin` | The same, and give the user the super-admin role |

### Videos

| Command         | Description                                       |
| --------------- | ------------------------------------------------- |
| `videos:import` | Import videos for a user                          |
| `videos:clear`  | Delete the files of videos that were soft-deleted |

### Playlists and media

| Command                                               | Description                                    |
| ----------------------------------------------------- | ---------------------------------------------- |
| `playlists:clear`                                     | Delete generated DASH playlists from storage   |
| `transcodes:clear`                                    | Permanently delete failed transcodes           |
| `transcodes:clear --all`                              | Permanently delete all expired transcodes      |
| `groups:clear`                                        | Remove all videos from groups of a given type  |
| `media-library:regenerate --only-missing --queue-all` | Regenerate missing thumbnails and other images |

### Search

| Command                    | Description                                                    |
| -------------------------- | -------------------------------------------------------------- |
| `scout:sync`               | Set up the Typesense collections                               |
| `scout:sync --import`      | The same, and import all records                               |
| `scout:sync --delete`      | Delete all indexes and set them up again (removes their data)  |
| `scout:delete-index Model` | Delete the index of one model, for example when it's corrupted |
| `scout:import Model`       | Import one model into its search index                         |

### Settings

| Command                | Description                                                                                                                                   |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `settings:clear-cache` | Clear the cached admin settings. Run it after a settings migration (see [Application Configuration](configuration.md#admin-managed-settings)) |

## Using Podman directly

```bash
podman exec -it systemd-stry php artisan help
podman exec -it systemd-stry-horizon /bin/bash
```

## See also

- [Podman Quadlet](podman.md): service names, installing and secrets
- [Application Configuration](configuration.md): app settings
- [Object Storage (S3)](s3.md): where media files are stored

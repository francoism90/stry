---
title: Interaction
order: 5
tags:
  - fish
  - shell
  - bash
  - commands
---

## Interaction

Stry provides a shell utility, which is a copy of [Laravel Sail](https://github.com/laravel/sail/blob/1.x/bin/sail) with adjustments made for usage with Podman Quadlet.

> **NOTE**: The utility can be used with production and development environments.

To install, create a shell `alias`, e.g. when using [fish-shell](https://fishshell.com/docs/current/cmds/alias.html):

```fish
alias --save stry '~/projects/stry/bin/quadlet'
```

This allows global interacting with Podman containers, using the same logic as Laravel Sail:

```fish
stry help
stry shell
stry tinker
stry artisan optimize
stry a migrate
stry a horizon:forget --all
stry a videos:import
stry redis flushall
stry garage bucket list
```

To interact with containers without the alias:

```bash
podman exec -it systemd-stry php artisan help
podman exec -it systemd-stry-queue /bin/bash
podman exec -ti systemd-stry-redis /bin/bash
```

## Commands

See `stry help` for a complete overview:

| Command | Description |
|---|---|
| `stry a users:create` | Creates a new user. |
| `stry a videos:import` | Import videos for an user. |
| `stry a videos:clear` | Remove(!) soft-deleted videos from filesystem. |
| `stry a tags:create` | Create a new tag. |
| `stry a tags:sort` | Sort tags alphabetically, based on type. |
| `stry a playlists:clear` | Remove generated HLS-playlist from filesystem. |
| `stry a groups:clear` | Detach all videos from groups of a given type. |
| `stry a scout:sync --flush` | Sync searchable model indexes. |
| `stry a media-library:regenerate --only-missing --queue-all` | Regenerate missing conversions |

---
title: Interaction
order: 4
tags:
  - fish
  - shell
  - management
---

## Interaction

Stry provides a shell utility, which is a copy of [Laravel Sail](https://github.com/laravel/sail/blob/1.x/bin/sail) with adjustments made for usage with Podman Quadlet.

> **TIP**: It can be used on production and development environments.

To install, create a shell `alias`, e.g. when using [fish-shell](https://fishshell.com/docs/current/cmds/alias.html):

```fish
alias --save stry '~/projects/stry/bin/quadlet'
```

This allows global interacting with the app container, using the same logic as Laravel Sail:

```fish
stry help
stry shell
stry tinker
stry artisan optimize
stry a migrate
stry a videos:import
```

To interact with the app container without the utility:

```bash
podman exec -it systemd-stry php artisan help
```

## Commands

See `stry help` for a complete overview:

| Command | Description |
|---|---|
| `stry a users:create` | Creates a new user |
| `stry a videos:import` | Import videos to an user |
| `stry a videos:clean` | Remove soft-deleted videos from filesystem (!) |
| `stry a tags:create` | Create a new tag |
| `stry a tags:sort` | Sort tags alphabetically |
| `stry a playlist:clear` | Remove generated HLS-playlist from filesystem |
| `stry a scout:sync --delete` | Sync Scout (search) model indexes, useful on Meilisearch upgrades. |

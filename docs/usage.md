---
title: Using Stry
order: 4
tags:
  - stry
  - managing
---

## Usage

1. Make sure to reload systemd on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Make sure the minimal required dependencies have been created and running:

```bash
systemctl --user restart stry-minio stry-pgsql stry-redis
```

## Shell utility

Stry provides a shell utility, which is a copy of [Laravel Sail](https://github.com/laravel/sail/blob/1.x/bin/sail) with adjustments made for usage with Podman Quadlet.

To install, create a shell `alias`, e.g. when using [fish-shell](https://fishshell.com/docs/current/cmds/alias.html):

```fish
alias --save stry '~/projects/stry/bin/quadlet'
```

This allows interacting with the app container using the same logic like Laravel Sail:

```fish
stry help
stry shell
stry tinker
stry a migrate
stry a videos:import
stry a videos:clean
stry a tags:create
stry a users:create
```

To interact with the container without the utility:

```bash
podman exec -it systemd-stry php artisan help
```

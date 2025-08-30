---
title: Development
order: 2
tags:
  - vscode
  - podman
  - devcontainer
  - boost
---

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..)
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- [VSCode](https://code.visualstudio.com/) with [Podman extension](https://github.com/jorchube/devcontainer-definitions).
- [GitHub Copilot](https://github.com/features/copilot) (optional)

## Installation

1. Clone project to a working directory (i.e. `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

1. Setup [Podman Quadlet](podman.md).

1. Change `CONTAINER_ENV` to `development` in `~/.config/containers/systemd/stry/config/app.env`.

1. Add the `app` volume to `stry.container`, `stry-queue.container`, `stry-reverb.container` and `stry-schedule.container`:

```diff
+Volume=${APP_PATH}:/app:rw,z,U
Volume=${DATA_PATH}:/data:rw,z,U
```

**NOTE**: The volume `U` flag should only be appended in `stry.container`.

1. Open the cloned project with VSCode as a devcontainer.

1. Perform the following commands in the VSCode terminal:

```bash
composer install
php artisan storage:link
php artisan key:generate
php artisan google-fonts:fetch
php artisan wayfinder:generate
php artisan migrate --seed
pnpm install
```

1. Setup a [proxy](proxy.md).

1. Run the Vite watcher:

```bash
stry pnpm dev
```

## IDE integration

To offer better [IDE integration](https://github.com/barryvdh/laravel-ide-helper) with Laravel:

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models --nowrite
```

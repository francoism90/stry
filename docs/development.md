---
title: Development
order: 2
tags:
  - vscode
  - podman
  - devcontainer
  - boost
  - ai
---

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..)
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- [VSCode](https://code.visualstudio.com/) with [Podman extension](https://github.com/jorchube/devcontainer-definitions).
- [GitHub Copilot](https://github.com/features/copilot) (optional)

## Installation

### Setup project

Clone project to a working directory (i.e. `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

### Podman

See [Podman Quadlet](podman.md) guide for details, and setup with the following adjustments:

1. Change `CONTAINER_ENV` to `development` in `~/.config/containers/systemd/stry/config/app.env`.

1. Append the `app` volume to `stry.container`, `stry-queue.container`, `stry-reverb.container` and `stry-schedule.container` containers:

```diff
+Volume=${APP_PATH}:/app:rw,z,U
Volume=${DATA_PATH}:/data:rw,z,U
```

**NOTE**: The volume `U` flag should only be appended in `stry.container`.

1. Remove `Wants=` `stry-ssr.container` from the same container files.

### Using devcontainer

Open the cloned project with VSCode as a devcontainer.

1. Perform the following commands in the VSCode terminal:

```bash
composer install
php artisan storage:link
php artisan key:generate
php artisan migrate --seed
php artisan google-fonts:fetch
pnpm install
```

### Setup proxy

Follow the [proxy](proxy.md) guide for more information.

### Using watchers

Run the Vite watcher:

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

## Laravel Boost

To use [Laravel Boost](https://boost.laravel.com/installed):

1. Open the Command Palette (Cmd+Shift+P or Ctrl+Shift+P)
1. Press enter on "MCP: List Servers"
1. Arrow to laravel-boost and press enter
1. Choose 'Start server' and you're good to go!

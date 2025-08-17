---
title: Podman Quadlet
order: 2
tags:
  - vscode
  - podman
  - devcontainer
---

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..)
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- [VSCode](https://code.visualstudio.com/) with [Podman extension](https://github.com/jorchube/devcontainer-definitions).

## Installation

1. Clone project to a working directory (i.e. `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

1. Setup [Podman](podman.md).

1. Change `APP_ENV` to `development`.

1. Add the app bind mount to every container prefixed with `stry`:

```docker
Volume=${APP_PATH}:/app:rw,z,U
```

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

1. Run the vite watcher:

```bash
stry pnpm dev
```

---
title: Podman Quadlet
order: 2
tags:
  - podman
  - vscode
  - code
  - devcontainer
---

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.
- [VSCode](https://code.visualstudio.com/) with Podman support.

## Installation

1. Follow the [Podman](podman.md) guide.

1. Change `APP_ENV` to `development`.

1. Open the project with VSCode as a devcontainer.

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

1. Run the vite watcher:

```bash
stry pnpm dev
```

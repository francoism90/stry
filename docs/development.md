---
title: Podman Quadlet
order: 1
tags:
  - podman
  - quadlet
  - docker
  - devcontainer
---

To learn more about Podman Quadlet, please consider reading the following resources first:

- <https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html>
- <https://www.redhat.com/sysadmin/quadlet-podman>
- <https://mo8it.com/blog/quadlet/>

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

It's recommend running a rootless setup (this may already be setup by your distro):

- <https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md>
- <https://wiki.archlinux.org/title/Podman#Rootless_Podman>

## Installation

1. Setup the Quadlet containers:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/systemd/sty ~/.config/containers/
```

1. Append the local volume to `stry.container`, `stry-queue.container`, `stry-reverb.container` and `stry-schedule.container`:

```diff
+Volume=${APP_PATH}:/app:rw,z,U
```

1. Build the container image:

```bash
cd ~/projects/stry
podman build -t stry:latest --target=development .
```

1. Adjust the environment configuration:

```bash
cd ~/.config/containers/systemd/stry/config
vi app.env postgres.env minio.env ..
```

1. Make sure the project environment are in sync:

```bash
cp ~/projects/stry/.env.example ~/projects/stry/.env
vi ~/projects/stry/.env
```

1. You may need to set a SELinux Policy file context on writeable paths:

```bash
sudo semanage fcontext -a -t container_file_t '/var/home/user/projects/stry/storage/app/import(/.*)?'
sudo restorecon -R -v /var/home/user/projects/stry/storage/app/import
```

1. Make sure to reload systemd on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Make sure the minimal required dependencies have been created and running:

```bash
systemctl --user restart stry-minio stry-pgsql stry-redis
```

1. Setup [MinIO](minio.md).

1. Open the project with VSCode and run it as a devcontainer.

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

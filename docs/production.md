---
title: Podman Quadlet
order: 1
tags:
  - podman
  - quadlet
  - docker
  - systemd
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

1. Replace the `APP_PATH` mount of `stry.container`, `stry-queue.container`, `stry-reverb.container` and `stry-schedule.container`, with `DATA_PATH`:

```diff
-Volume=${APP_PATH}:/app:rw,z,U
+Volume=${DATA_PATH}:/app/storage/app:rw,z,U
```

```diff
-Volume=${APP_PATH}:/app:rw,z
+Volume=${DATA_PATH}:/app/storage/app:rw,z
```

If you want to use different paths, append:

```diff
+Volume=${MEDIA_PATH}:/app/storage/app/media:rw,z,U
+Volume=${IMPORT_PATH}:/app/storage/app/import:rw,z,U
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
sudo semanage fcontext -a -t container_file_t '/var/home/user/data/stry(/.*)?'
sudo restorecon -R -v /var/home/user/data/stry
```

1. Make sure to reload systemd on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Build the container image:

```bash
cd ~/projects/stry
podman build -t stry:latest --target=production .
```

1. Make sure the minimal required dependencies have been created and running:

```bash
systemctl --user restart stry-minio stry-pgsql stry-redis
```

1. Setup [MinIO](minio.md).

1. See [interaction](interaction.md) to setup management.

1. Perform the following commands with:

```bash
stry a key:generate
stry a google-fonts:fetch
stry a wayfinder:generate
stry a migrate --seed
stry pnpm build
```

1. HLS generating can be configured in `config/playlist.php` (such as formats to use) or by setting environment variables.

---
title: Podman Quadlet
order: 3
tags:
  - podman
  - quadlet
  - compose
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

1. Make sure to reload on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Setup [MinIO](minio.md).

1. Setup a [proxy](proxy.md).

1. Rebuilt the container:

```bash
systemctl --user restart stry
```

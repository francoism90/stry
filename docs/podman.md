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

This guide assumes a rootless setup (this may already be configured by your distro):

- <https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md>
- <https://wiki.archlinux.org/title/Podman#Rootless_Podman>

## Installation

1. Setup the Quadlet containers:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/systemd/stry ~/.config/containers/systemd/
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

1. Make sure to reload on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Setup [MinIO](minio.md).

1. Rebuilt the container:

> **NOTE**: The first start can take a significance of time. It will install the vendor packages, and run [storage-chown-by-maps](https://github.com/containers/podman/issues/13071).
> It's important to not cancel this process, or increase the `timeout=*` value to a higher value if needed by the setup.

```bash
systemctl --user restart stry
```

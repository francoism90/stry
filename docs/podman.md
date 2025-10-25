---
title: Podman Quadlet
order: 3
tags:
  - podman
  - quadlet
  - containers
  - systemd
  - compose
---

## Introduction

To learn more about Podman Quadlet, please consider reading the following resources first:

- <https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html>
- <https://www.redhat.com/sysadmin/quadlet-podman>
- <https://mo8it.com/blog/quadlet/>

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

This guide assumes a rootless setup (this may already be configured by your distribution or administrator):

- <https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md>
- <https://wiki.archlinux.org/title/Podman#Rootless_Podman>

## Installation

### Containers

Copy the container files:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/systemd/stry ~/.config/containers/systemd/
```

Adjust the container configurations:

```bash
cd ~/.config/containers/systemd/stry/config
vi app.env postgres.env typesense.env ..
```

1. Make sure the project environment is aligned with the changes made:

```bash
cp ~/projects/stry/.env.example ~/projects/stry/.env
vi ~/projects/stry/.env
```

### Storage

1. Make sure the data path (`DATA_PATH`) exists as defined in`app.env`:

```bash
mkdir -p /home/user/data/stry
```

1. Reload the container configurations on changes:

```bash
systemctl --user daemon-reload
```

1. Follow the [S3 object-storage](s3.md) guide.

### Rebuild containers

> **NOTE**: The first start can take a significance of time. It will install the vendor packages, and run [storage-chown-by-maps](https://github.com/containers/podman/issues/13071).
> It's important to not cancel this process, or increase the `timeout=*` value to a higher value if needed by the setup.

To rebuild or (re)start the containers:

```bash
systemctl --user restart stry
```

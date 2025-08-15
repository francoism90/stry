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

1. Build the Docker images:

```bash
cd ~/projects/stry
podman build -t stry:latest --target=production .
```

1. Adjust the containers services, remove the `APP_PATH` mount:

```diff
-Volume=${APP_PATH}:/app:rw,z
-Volume=${APP_PATH}:/app:rw,z,U
```

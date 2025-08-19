---
title: Running on production
order: 1
tags:
  - podman
  - quadlet
  - usage
---

## Prerequisites

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

## Installation

1. Clone project to a working directory (i.e. `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

1. Setup [Podman](podman.md).

1. Setup a [proxy](proxy.md).

1. See [interaction](interaction.md) for management.

## Tips

You may need to limit the [resources](https://docs.podman.io/en/v4.6.0/markdown/podman-update.1.html#cpus-number) available for a container, this can be done realtime:

```bash
podman update systemd-stry-queue --cpus=5
```

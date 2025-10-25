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

### Setup project

Clone project to a working directory (i.e. `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

### Podman

See [Podman Quadlet](podman.md) guide for details.

### Proxy

A proxy is required to interact with the container services.

See the [proxy](proxy.md) guide for details.

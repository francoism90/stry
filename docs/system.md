---
title: System Configuration
order: 1
tags:
  - podman
  - quadlet
  - containers
  - resources
---

## Resource limiting

You may need to limit the [resources](https://docs.podman.io/en/latest/markdown/podman-update.1.html#cpus-number) available for a container, this can be done realtime or by adding `PodmanArgs=--cpus=5` to the desired container file:

```bash
podman update systemd-stry-queue --cpus=5
```

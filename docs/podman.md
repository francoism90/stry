---
title: Podman Quadlet
order: 1
tags:
  - podman
  - quadlet
  - systemd
  - caddy
---

To learn more about Podman Quadlet, please consider reading the following resources first:

- <https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html>
- <https://www.redhat.com/sysadmin/quadlet-podman>
- <https://mo8it.com/blog/quadlet/>

## Prerequisites

- Linux (Debian, Fedora, Arch, CentOS, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

It's recommend running a rootless setup:

- <https://gitstry.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md>
- <https://wiki.archlinux.org/title/Podman#Rootless_Podman>

## Installation

### Build containers

Build the Docker images (this may take some time):

```bash
cd ~/projects/stry
./bin/build-containers
```

### Systemd units

Copy the `systemd` directory to `~/.config/containers`, verify the path `~/.config/containers/systemd/stry` exists:

```bash
mkdir -p ~/.config/containers/
cp -r ~/stry/containers/systemd ~/.config/containers/
```

Adjust all environment files in `~/.config/containers/systemd/stry/config`, update `~/projects/stry/.env` to reflect any changes:

```bash
cd ~/.config/containers/systemd/stry/config
vi app.env postgres.env ..
```

### Configure Proxy

[Caddy](https://caddyserver.com/) is used as proxy. However you are free to use something else.

The given configuration assumes you want to use self-signed certificates:

```bash
cd ~/.config/containers/systemd/proxy/config
vi Caddyfile sites/stry.caddy
systemctl --user enable podman.socket --now
systemctl --user daemon-reload
systemctl --user start proxy`
```

Make sure to append the following entries to your hosts (`/etc/hosts`) file:

```text
# stry
127.0.0.1 stry.test ws.stry.test vite.stry.test
::1 stry.test ws.stry.test vite.stry.test
```

> Tip: You may want to use [AdGuard Home](https://adguard.com/en/adguard-home/overview.html) instead, and rewrite `stry.test` & `*stry.test` requests to your (homelab) server.

To copy the generated Caddy CA that can be imported into your browsers certificate keychain:

```bash
podman cp systemd-proxy:/data/caddy/pki/authorities/local/root.crt ~/Downloads/caddy.crt
```

## Usage

Make sure to reload systemd on configuration changes:

```bash
systemctl --user daemon-reload
```

On first install, make sure the network has been created:

```bash
systemctl --user start stry-network.service
```

## Shell utility

Stry provides a shell utility, which is a copy of [Laravel Sail](https://gitstry.com/laravel/sail/blob/1.x/bin/sail) with adjustments made for the usage with Podman Quadlet.

To install, create a shell `alias`, e.g. when using [fish-shell](https://fishshell.com/docs/current/cmds/alias.html):

```fish
alias --save stry '~/projects/stry/bin/quadlet'
```

This allows interacting with the app container using the same logic like Laravel Sail:

```fish
stry help
stry shell
stry tinker
stry a migrate
```

To interact with the container without the utility:

```bash
podman exec -it systemd-stry php artisan help
```

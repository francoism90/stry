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

- Linux (Debian, Fedora, CentOS, Arch, Ubuntu, ..).
- [Podman 5.3 or higher](https://podman.io/) with Quadlet (systemd) support.

It's recommend running a rootless setup (this may already be setup by your distro):

- <https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md>
- <https://wiki.archlinux.org/title/Podman#Rootless_Podman>

## Installation

1. Build the Docker images (this may take some time):

```bash
cd ~/projects/stry
./bin/build-containers
```

1. Setup the containers:

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
sudo semanage fcontext -a -t container_file_t '/var/home/user/projects/stry/storage/app/import(/.*)?'
sudo restorecon -R -v /var/home/user/projects/stry/storage/app/import
```

### Configure Proxy

[Caddy](https://caddyserver.com/) is used as proxy, however you are free to use something else (i.e. traefik, nginx).

1. Setup the Podman containers:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/systemd/proxy ~/.config/containers/
```

1. The given configuration assumes you want to use self-signed certificates:

```bash
cd ~/.config/containers/systemd/proxy/config
vi Caddyfile sites/stry.caddy
```

1. Start the proxy:

```bash
systemctl --user enable podman.socket --now
systemctl --user daemon-reload
systemctl --user start proxy`
```

Make sure to append the following entries to your hosts (`/etc/hosts`) file:

```text
127.0.0.1 stry.test ws.stry.test vite.stry.test s3.stry.test
::1 stry.test ws.stry.test vite.stry.test s3.stry.test
```

> **TIP:** You may want to use [AdGuard Home](https://adguard.com/en/adguard-home/overview.html) when using a homelab, and rewrite `stry.test` & `*.stry.test` requests to your server instead.

1. Copy the generated Caddy CA, and import into your browsers certificate trust keychain:

```bash
podman cp systemd-proxy:/data/caddy/pki/authorities/local/root.crt ~/Downloads/proxy.crt
```

## Usage

1. Make sure to reload systemd on configuration changes:

```bash
systemctl --user daemon-reload
```

1. Make sure the minimal required dependencies have been created and running:

```bash
systemctl --user restart stry-minio stry-pgsql stry-redis
```

## Shell utility

Stry provides a shell utility, which is a copy of [Laravel Sail](https://github.com/laravel/sail/blob/1.x/bin/sail) with adjustments made for usage with Podman Quadlet.

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
stry a videos:import
```

To interact with the container without the utility:

```bash
podman exec -it systemd-stry php artisan help
```

---
title: Proxy
order: 4
tags:
  - proxy
  - caddy
---

## Introduction

[Caddy](https://caddyserver.com/) is used for container proxying and running it locally, however you are free to use something else (i.e. traefik, nginx) and even host on a VPS.

## Configure Proxy

1. Setup the Caddy container:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/systemd/proxy ~/.config/containers/systemd/
```

1. Configure the proxy:

```bash
cd ~/.config/containers/systemd/proxy/config
vi Caddyfile sites/stry.caddy
```

1. Start the proxy:

```bash
systemctl --user daemon-reload
systemctl --user restart proxy
```

When running locally, make sure to append the following entries to your hosts-file (`/etc/hosts`):

```text
127.0.0.1 stry.test ws.stry.test vite.stry.test s3.stry.test mc.stry.test
::1 stry.test ws.stry.test vite.stry.test s3.stry.test mc.stry.test
```

> **TIP**: You may want to use [AdGuard Home](https://adguard.com/en/adguard-home/overview.html) when using a homelab, and rewrite `stry.test` & `*.stry.test` requests to your server instead.

1. Copy the generated Caddy CA, and import into your browsers certificate trust keychain:

```bash
podman cp systemd-proxy:/data/caddy/pki/authorities/local/root.crt ~/proxy.crt
```

---
title: Proxy
order: 3
tags:
  - proxy
  - caddy
---

## Configure Proxy

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

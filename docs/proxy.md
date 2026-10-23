---
title: Proxy
section: Configuration
order: 4
tags:
    - proxy
    - caddy
---

# Reverse Proxy

**stry** doesn't come with a separate proxy container. The app already runs FrankenPHP, which includes the Caddy web server, and that Caddy instance forwards requests to the other services itself.

It works by path and by hostname. You send the app's domain and every subdomain below to the app on port `8000`. Caddy then decides where each request goes, by the path first and otherwise by the `Host` header.

These services run under a path of the app's own domain, so they need no extra DNS entries or certificates:

| Environment variable | `.env.example` | Sent to                      | Used for                   |
| -------------------- | -------------- | ---------------------------- | -------------------------- |
| `VITE_REVERB_PATH`   | `/ws`          | `systemd-{app}-reverb:6001`  | Laravel Reverb (WebSocket) |
| `MAILPIT_PATH`       | `/mail`        | `systemd-{app}-mailpit:8025` | Mailpit web UI             |

Leave a path empty to turn that route off. Mailpit serves its UI from `MAILPIT_PATH` itself (`MP_WEBROOT`), so regenerate and reinstall `mailpit.quadlets` after changing it. Reverb keeps serving from `/`: Caddy removes the path before passing the request on, and Echo connects to it through `wsPath`.

These go by hostname:

| Environment variable               | Sent to                      | Used for                   |
| ---------------------------------- | ---------------------------- | -------------------------- |
| The host in `APP_URL`              | the app itself               | The main application       |
| The host in `AWS_URL`              | `systemd-{app}-rustfs:9000`  | S3-compatible storage API  |
| `RUSTFS_CONSOLE_HOST` (optional)   | `systemd-{app}-rustfs:9001`  | RustFS web console         |
| `VITE_HMR_HOST` (optional)         | `systemd-{app}-vite:5173`    | Vite dev server (HMR)      |

Signed S3 links and the Vite dev server only work from the root of a domain, which is why these keep their own subdomain.

Both are set in `config/octane.php` and turned into Caddy config by laravel-podman: `PodmanCaddyRoutes` for the paths (`CADDY_SERVER_EXTRA_DIRECTIVES`) and `PodmanCaddySites::render()` for the subdomains (`CADDY_EXTRA_CONFIG`). If you leave `RUSTFS_CONSOLE_HOST` or `VITE_HMR_HOST` empty, that service isn't exposed at all: `render()` skips any entry without a hostname.

## Bring your own TLS termination

Handle HTTPS in front of port `8000` with whatever you already use: the reverse proxy on your router or NAS (Synology, pfSense, OPNsense), Nginx Proxy Manager, Traefik, Cloudflare Tunnel and so on. Point the app's domain and every subdomain above at the same host and port. You don't need to open a port per service, because the app's Caddy tells them apart by the `Host` header.

:::warning
Your TLS proxy must pass the original `Host` header through unchanged. The app's Caddy uses it to route each request.
:::

## Adding another service

Add the service's path and its internal `host:port` as a `->route()` on `PodmanCaddyRoutes` in `config/octane.php`, or its public hostname to the map passed to `PodmanCaddySites::render()`. That's all: you don't need a new Quadlet unit, host port or proxy entry.

## Using a different port

Port `8000` is the port FrankenPHP listens on inside the container (`--port=8000` in `APP_COMMAND`). `OCTANE_PORT` in `config/octane.php` must match it, because `PodmanCaddySites` uses it to build the hostname rules.

To use a different port on the host, you don't need to change either of those. In `app.quadlets`, `PublishPort=8001:8000` keeps FrankenPHP on port `8000` inside the container and makes it available on port `8001` on the host. Then point your reverse proxy and firewall at port `8001`.

Only change `OCTANE_PORT` (and the `--port` in `APP_COMMAND`) if FrankenPHP itself needs to listen on a different port inside the container.

## Local development

You don't need any of this locally. The app is available at `http://localhost:8000`. See [Development Setup](development.md).

## Troubleshooting

- **WebSockets don't connect**: set `VITE_REVERB_HOST` to the app's own host and `VITE_REVERB_PATH=/ws`, then rebuild the frontend. Your TLS proxy must also forward WebSocket upgrades.
- **A service returns 404 or refuses the connection**: check that its variable (`AWS_URL`, `RUSTFS_CONSOLE_HOST` or `VITE_HMR_HOST`) contains exactly the hostname your reverse proxy forwards, or that `VITE_REVERB_PATH` and `MAILPIT_PATH` are set. Also check that the service's container is running and reachable on the app's internal network.
- **The app works, but `s3.*` and similar subdomains don't**: check that your reverse proxy passes the `Host` header through unchanged, instead of replacing it with the app's own hostname.

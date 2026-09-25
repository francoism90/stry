---
title: Proxy
sidebar_position: 7
tags:
    - proxy
    - caddy
---

# Reverse Proxy

**stry** doesn't come with a separate proxy container. The app already runs FrankenPHP, which includes the Caddy web server, and that Caddy instance forwards requests to the other services itself.

It works by hostname. You send every subdomain to the app on port `8000`, and Caddy looks at the `Host` header to decide where each request goes:

| Environment variable               | Sent to                      | Used for                   |
| ---------------------------------- | ---------------------------- | -------------------------- |
| The host in `APP_URL`              | the app itself               | The main application       |
| The host in `AWS_URL`              | `systemd-{app}-rustfs:9000`  | S3-compatible storage API  |
| `VITE_REVERB_HOST` / `REVERB_HOST` | `systemd-{app}-reverb:6001`  | Laravel Reverb (WebSocket) |
| `MAILPIT_UI_HOST` (optional)       | `systemd-{app}-mailpit:8025` | Mailpit web UI             |

This mapping is set in `config/octane.php` (`caddy.env.CADDY_EXTRA_CONFIG`) and turned into Caddy config by `Support\Octane\CaddySites`. If you leave `MAILPIT_UI_HOST` empty, Mailpit isn't exposed at all: `CaddySites::render()` skips any entry without a hostname.

## Bring your own TLS termination

Handle HTTPS in front of port `8000` with whatever you already use: the reverse proxy on your router or NAS (Synology, pfSense, OPNsense), Nginx Proxy Manager, Traefik, Cloudflare Tunnel and so on. Point every subdomain above at the same host and port. You don't need to open a port per service, because the app's Caddy tells them apart by the `Host` header.

:::warning
Your TLS proxy must pass the original `Host` header through unchanged. The app's Caddy uses it to route each request.
:::

## Adding another service

Add the service's public hostname and its internal `host:port` to the map passed to `CaddySites::render()` in `config/octane.php`. That's all: you don't need a new Quadlet unit, host port or proxy entry.

## Using a different port

Port `8000` is the port FrankenPHP listens on inside the container (`--port=8000` in `APP_COMMAND`). `OCTANE_PORT` in `config/octane.php` must match it, because `CaddySites` uses it to build the hostname rules.

To use a different port on the host, you don't need to change either of those. In `app.quadlets`, `PublishPort=8001:8000` keeps FrankenPHP on port `8000` inside the container and makes it available on port `8001` on the host. Then point your reverse proxy and firewall at port `8001`.

Only change `OCTANE_PORT` (and the `--port` in `APP_COMMAND`) if FrankenPHP itself needs to listen on a different port inside the container.

## Local development

You don't need any of this locally. The app is available at `http://localhost:8000`. See [Development Setup](development.md).

## Troubleshooting

- **A service returns 404 or refuses the connection**: check that its variable (`AWS_URL`, `VITE_REVERB_HOST`, `MAILPIT_UI_HOST`) contains exactly the hostname your reverse proxy forwards. Also check that the service's container is running and reachable on the app's internal network.
- **The app works, but `ws.*`, `s3.*` and similar subdomains don't**: check that your reverse proxy passes the `Host` header through unchanged, instead of replacing it with the app's own hostname.

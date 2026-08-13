---
title: Proxy
order: 4
tags:
  - proxy
  - caddy
---

# Reverse Proxy

**stry** doesn't bundle a dedicated proxy container. Instead, the app's own FrankenPHP/Caddy instance (started by `octane:frankenphp`) reverse proxies sibling services directly, driven by `config/octane.php`'s `caddy.env.CADDY_EXTRA_CONFIG` (rendered by `Support\Octane\CaddySites`). One upstream — the app's `:8000` — is enough for every subdomain you use; the app's own Caddy routes by `Host` header:

| Env var | Routes to | Purpose |
| --- | --- | --- |
| `APP_URL`'s host | the app itself | Main application |
| `AWS_URL`'s host | `systemd-{app}-rustfs:9000` | S3-compatible API |
| `VITE_REVERB_HOST` / `REVERB_HOST` | `systemd-{app}-reverb:6001` | Laravel Reverb (WebSocket) |
| `MAILPIT_UI_HOST` (optional) | `systemd-{app}-mailpit:8025` | Mailpit UI |

Leave `MAILPIT_UI_HOST` unset to keep that block out of the config entirely — see `CaddySites::render()`, which skips any entry with an empty host.

## Bring your own TLS termination

Terminate HTTPS in front of `:8000` with whatever you already run — a router/NAS reverse proxy (Synology, pfSense/OPNsense), Nginx Proxy Manager, Traefik, Cloudflare Tunnel, etc. Point every subdomain above at the same destination host:port; no per-service port needs to be exposed, since the app's Caddy tells them apart by `Host` header alone.

> [!WARNING]
> Whatever terminates TLS must forward the original `Host` header unmodified — that's what the app's Caddy instance matches on.

## Adding another sibling service

Extend the map passed to `CaddySites::render()` in `config/octane.php` with the service's public hostname and its internal `host:port`. Nothing else — no Quadlet, no host port, no reverse-proxy entry — needs to change anywhere else.

## Using a different port

The `:8000` above is the container-internal port FrankenPHP listens on (`--port=8000` in `APP_COMMAND`) — `config/octane.php`'s `OCTANE_PORT` must match it, since that's what `CaddySites` uses to build the `Host`-matched blocks.

You don't need to change either of those just to publish on a different host port. In `app.quadlets`, `PublishPort=8001:8000` keeps FrankenPHP on `:8000` internally and only remaps the host side to `:8001` — then point your reverse proxy (and firewall) at `:8001` instead.

Only set `OCTANE_PORT` (and change `APP_COMMAND`'s `--port`) if you need FrankenPHP itself to listen on a different port inside the container.

## Local development

Local development doesn't need any of this — the app is reachable directly at `http://localhost:8000`, see [Development Setup](development.md).

## Troubleshooting

- **404 / connection refused for a sibling service** — confirm its env var (`AWS_URL`, `VITE_REVERB_HOST`, `MAILPIT_UI_HOST`) resolves to the exact hostname your upstream reverse proxy is forwarding, and that the sibling container is running and reachable on the app's internal network.
- **Works for the app but not `ws.*`/`s3.*`/etc.** — check that your upstream reverse proxy forwards the `Host` header as-is rather than rewriting it to the app's own hostname.

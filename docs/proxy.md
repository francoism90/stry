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

It works by hostname. You send every subdomain to the app on port `8000`, and Caddy looks at the `Host` header to decide where each request goes:

| Environment variable               | Sent to                      | Used for                   |
| ---------------------------------- | ---------------------------- | -------------------------- |
| The host in `APP_URL`              | the app itself               | The main application       |
| The host in `AWS_URL`              | `systemd-{app}-rustfs:9000`  | S3-compatible storage API  |
| `MAILPIT_UI_HOST` (optional)       | `systemd-{app}-mailpit:8025` | Mailpit web UI             |
| `RUSTFS_CONSOLE_HOST` (optional)   | `systemd-{app}-rustfs:9001`  | RustFS web console         |
| `VITE_HMR_HOST` (optional)         | `systemd-{app}-vite:5173`    | Vite dev server (HMR)      |
| `VITE_REVERB_HOST` / `REVERB_HOST` | `systemd-{app}-reverb:6001`  | Laravel Reverb (WebSocket) |

This mapping is set in `config/octane.php` (`caddy.env.CADDY_EXTRA_CONFIG`) and turned into Caddy config by `Support\Octane\CaddySites`. If you leave `MAILPIT_UI_HOST`, `RUSTFS_CONSOLE_HOST` or `VITE_HMR_HOST` empty, that service isn't exposed at all: `CaddySites::render()` skips any entry without a hostname.

## With the Pods proxy

The simplest setup is the [Pods proxy](https://github.com/foxws/pods#proxy), a Caddy container that routes each hostname straight to the right container, so nothing needs a port on the host. `routes:` in `pods.yml` names stry's containers: the app, Reverb and RustFS's S3 API, and in development also Vite, the Mailpit web UI and the RustFS console.

- **With Pods**, the install page asks for a hostname for each route, and the Routes card on the application page changes them later. Leave the development ones empty in production.
- **Without Pods**, give lpod the hostnames yourself:

```bash
echo '{"routes": [
    {"role": "app", "host": "stry.example.com", "upstream": "systemd-stry:8000"},
    {"role": "ws", "host": "ws.stry.example.com", "upstream": "systemd-stry-reverb:6001", "via": "main"},
    {"role": "s3", "host": "s3.stry.example.com", "upstream": "systemd-stry-rustfs:9000", "via": "main"}
]}' | lpod stry routes set --stdin --json
```

lpod writes `~/.config/pods-proxy/sites/stry.Caddyfile`, lets the proxy join `stry.network`, and reloads it. The `ws` and `s3` routes go `via: main`, to the app, whose own Caddy forwards them to Reverb and RustFS (see the table above), so they wake the app while it's on demand too. With one wildcard certificate (`*.home.example.com`, through a DNS challenge), lpod writes `sites/stry.caddy` handlers instead, and the hostnames must be one label under the wildcard, such as `stry-ws.home.example.com` and `stry-s3.home.example.com`. The hostnames must match `.env`: the host in `APP_URL`, `VITE_REVERB_HOST`, the host in `AWS_URL`, and in development `VITE_HMR_HOST`, `MAILPIT_UI_HOST` and `RUSTFS_CONSOLE_HOST`. The proxy gets certificates itself. Behind another proxy that handles HTTPS, set `Environment=PROXY_SCHEME=http` in a drop-in for `pods-proxy`.

## Bring your own TLS termination

The units don't publish port `8000` on the host, so publish it first with a drop-in (see [Podman Quadlet](podman.md#the-units)). Then handle HTTPS in front of it with whatever you already use: the reverse proxy on your router or NAS (Synology, pfSense, OPNsense), Nginx Proxy Manager, Traefik, Cloudflare Tunnel and so on. Point every subdomain above at the same host and port. You don't need to open a port per service, because the app's Caddy tells them apart by the `Host` header.

:::warning
Your TLS proxy must pass the original `Host` header through unchanged. The app's Caddy uses it to route each request.
:::

### Example: Caddy

With Caddy on the same host, list every hostname in one site and send it to port `8000`. Caddy passes the `Host` header through by default:

```caddy
example.com, s3.example.com, ws.example.com {
	reverse_proxy 127.0.0.1:8000 {
		stream_close_delay 5m
	}
}
```

Add `vite.example.com` when you run the Vite dev server behind it. `stream_close_delay` keeps WebSocket connections (Reverb, Vite HMR) open for a while after a Caddy reload, instead of closing them right away. For a server that's only reachable on your LAN, use a wildcard certificate through a DNS challenge instead, as Let's Encrypt can't reach it over HTTP.

Don't add `header_up Host {upstream_hostport}`, which some guides suggest: it replaces the hostname with `127.0.0.1:8000`, and every request ends up at the app.

## Adding another service

Add the service's public hostname and its internal `host:port` to the map passed to `CaddySites::render()` in `config/octane.php`. That's all: you don't need a new Quadlet unit, host port or proxy entry.

## Using a different port

Port `8000` is the port FrankenPHP listens on inside the container (`--port=8000` in `APP_COMMAND`). `OCTANE_PORT` in `config/octane.php` must match it, because `CaddySites` uses it to build the hostname rules.

To use a different port on the host, you don't need to change either of those. The drop-in that publishes the app picks the host port: `PublishPort=8001:8000` keeps FrankenPHP on port `8000` inside the container and makes it available on port `8001` on the host. Then point your reverse proxy and firewall at port `8001`.

Only change `OCTANE_PORT` (and the `--port` in `APP_COMMAND`) if FrankenPHP itself needs to listen on a different port inside the container.

## Local development

The development units don't publish any service either, so Vite's live reload, Reverb, RustFS and Mailpit also go through these hostnames locally. See [Development Setup](development.md).

## Troubleshooting

- **A service returns 404 or refuses the connection**: check that its variable (`AWS_URL`, `MAILPIT_UI_HOST`, `RUSTFS_CONSOLE_HOST`, `VITE_HMR_HOST`, `VITE_REVERB_HOST`) contains exactly the hostname your reverse proxy forwards. Also check that the service's container is running and reachable on the app's internal network.
- **The app works, but `ws.*`, `s3.*` and similar subdomains don't**: check that your reverse proxy passes the `Host` header through unchanged, instead of replacing it with the app's own hostname.

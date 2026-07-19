---
title: Proxy
order: 4
tags:
    - proxy
    - caddy
---

# Proxy Configuration

The `proxy` preset installs [Caddy](https://caddyserver.com/) for automatic HTTPS and routing — see [Podman Quadlet](podman.md) for the general install flow. Its Caddyfile (`containers/stubs/proxy/runtimes/`) routes these subdomains, based on `APP_URL`'s host (`{{appHost}}`):

| Subdomain            | Routes to                | Purpose                  |
| --------------------- | -------------------------- | --------------------------- |
| `{host}`               | `stry:8000`                 | Main application            |
| `vite.{host}`          | `stry:5173`                 | Vite dev server              |
| `ws.{host}`            | `stry-reverb:6001`           | Laravel Reverb (WebSocket)  |
| `s3.{host}`            | `stry-rustfs:9000`           | S3-compatible API           |
| `fs.{host}`            | `stry-rustfs:9001`           | RustFS console               |
| `mail.{host}`          | `stry-mailpit:8025`          | Mailpit UI (dev)             |

## Install

```bash
php artisan podman:generate proxy
lpod install proxy/proxy.quadlets --replace
lpod proxy up
```

## Local DNS

Add these to `/etc/hosts` (adjust the domain to match `APP_URL`):

```text
127.0.0.1 stry.test ws.stry.test vite.stry.test s3.stry.test fs.stry.test mail.stry.test
::1       stry.test ws.stry.test vite.stry.test s3.stry.test fs.stry.test mail.stry.test
```

For a homelab with multiple devices, use [AdGuard Home](https://adguard.com/en/adguard-home/overview.html) (or similar) to rewrite `*.stry.test` to your server's IP instead.

## Trusting the local certificate

Caddy issues a locally-trusted certificate automatically in development. Export and trust it once — see [Trusting the local certificate](https://github.com/foxws/laravel-podman/blob/main/docs/proxy.md#trusting-the-local-certificate) in the package docs.

> [!WARNING]
> For production, use a real certificate (e.g. Let's Encrypt) instead — see [Production Setup](production.md).

## Troubleshooting

- **Connection refused** — `systemctl --user status proxy`
- **404** — check the Caddyfile and that the upstream container is running
- **Certificate not trusted** — re-import the CA certificate and restart the browser

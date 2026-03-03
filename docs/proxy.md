---
title: Proxy
order: 4
tags:
    - proxy
    - caddy
---

# Proxy Configuration

## Introduction

[Caddy](https://caddyserver.com/) is the recommended reverse proxy for **stry**, providing automatic HTTPS and easy configuration.

> [!NOTE]
> While Caddy is recommended, you can use alternatives like Traefik or Nginx. This guide focuses on Caddy.

---

## ⚙️ Setup Caddy Proxy

### Install Container Files

Setup the Caddy container configuration:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/podman/systemd/proxy ~/.config/containers/systemd/
```

### Configure Caddy

Edit the Caddy configuration files:

```bash
cd ~/.config/containers/systemd/proxy/config
vi Caddyfile sites/stry.caddy
```

> [!TIP]
> Check the Caddyfile for domain names and adjust them to match your setup.

---

## 🚀 Starting the Proxy

Reload systemd and start the proxy service:

```bash
systemctl --user daemon-reload
systemctl --user restart proxy
```

### Verify Status

```bash
systemctl --user status proxy
journalctl --user -u proxy -f
```

---

## DNS Configuration

### Option 1: Local Development (Hosts File)

For local development, add these entries to `/etc/hosts`:

```text
127.0.0.1 stry.test ws.stry.test vite.stry.test s3.stry.test mc.stry.test
::1 stry.test ws.stry.test vite.stry.test s3.stry.test mc.stry.test
```

### Option 2: Homelab Setup (Recommended)

> [!TIP]
> **For Homelab Users:**
>
> Use [AdGuard Home](https://adguard.com/en/adguard-home/overview.html) for DNS management.
>
> Configure DNS rewrites to point:
>
> - `stry.test` → Your server IP
> - `*.stry.test` → Your server IP

This provides network-wide access without modifying hosts files on each device.

---

## SSL Certificate Setup

### Trust Self-Signed Certificate

For local development, export and trust Caddy's CA certificate:

#### 1. Export the Certificate

```bash
podman cp systemd-proxy:/data/caddy/pki/authorities/local/root.crt ~/proxy.crt
```

#### 2. Import to Browser/System

**macOS:**

```bash
sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain ~/proxy.crt
```

**Linux (Arch/Debian/Ubuntu):**

```bash
sudo cp ~/proxy.crt /usr/local/share/ca-certificates/caddy.crt
sudo update-ca-certificates
```

**Firefox:**

1. Settings → Privacy & Security → Certificates → View Certificates
2. Import `~/proxy.crt`
3. Trust for identifying websites

> [!WARNING]
> For production deployments, use proper SSL certificates from Let's Encrypt or a commercial CA.

---

## 🔍 Available Services

After setup, these services will be available:

| Service          | URL                      | Description             |
| ---------------- | ------------------------ | ----------------------- |
| 🏠 Main App      | <https://stry.test>      | Main application        |
| 🔌 WebSocket     | <https://ws.stry.test>   | Laravel Reverb          |
| ⚡ Vite Dev      | <https://vite.stry.test> | Vite development server |
| ☁️ S3 API        | <https://s3.stry.test>   | Object storage endpoint |
| 🗄️ MinIO Console | <https://mc.stry.test>   | S3 management interface |

---

## 💡 Troubleshooting

> [!TIP]
> **Common Issues:**
>
> - **Certificate not trusted**: Re-import the CA certificate and restart browser
> - **Connection refused**: Check proxy status with `systemctl --user status proxy`
> - **404 errors**: Verify Caddyfile configuration and upstream container status
> - **Port conflicts**: Ensure ports 80/443 are not in use by other services

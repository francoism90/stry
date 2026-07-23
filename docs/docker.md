---
title: Docker Compose
order: 8
tags:
  - docker
  - compose
  - containers
  - alternative
---

# Docker Compose Setup

## Overview

Docker Compose is an **alternative** to the recommended Podman/Quadlet setup. Use Docker Compose if:

- You prefer Docker over Podman
- You need cross-platform support (Linux, macOS, Windows)
- Your infrastructure is Docker-native

> [!NOTE]
> The recommended and tested setup for this project is [Podman Quadlet](podman.md). Docker Compose files are maintained on a best-effort basis and may require adjustments.

---

## Prerequisites

- 🐳 [Docker Engine](https://docs.docker.com/engine/install/) or [Docker Desktop](https://docs.docker.com/desktop/)
- 🛠️ [Docker Compose v2+](https://docs.docker.com/compose/install/) (included with Docker Desktop)
- 🔧 Basic tools: `git`, `bash`

---

## Setup

### Clone the Project

```bash
cd ~/projects
git clone https://github.com/francoism90/stry.git
cd stry
```

### Build the Application Image

```bash
docker build -f containers/Containerfile -t stry:latest .
```

### Create Environment Files

Create the config directory:

```bash
mkdir -p containers/config
```

**Required files to populate:**

| File                              | Purpose                                                                       |
| --------------------------------- | ----------------------------------------------------------------------------- |
| `containers/config/app.env`       | Application configuration (see [Application Configuration](configuration.md)) |
| `containers/config/postgres.env`  | PostgreSQL credentials                                                        |
| `containers/config/typesense.env` | Typesense configuration                                                       |
| `containers/config/rustfs.env`    | RustFS S3 storage credentials                                                 |

Copy `.env.example` as a template:

```bash
cp .env.example containers/config/app.env
vi containers/config/app.env
```

---

## Usage

### Without Reverse Proxy (Direct Port Access)

```bash
docker compose -f containers/docker/docker-compose.yml up -d
```

The application will be available at **http://localhost:8000**

### With Caddy Reverse Proxy (HTTPS)

`docker-compose.proxy.yml` adds a `proxy` (Caddy) service on top of the base file — see [Proxy Configuration](proxy.md) for the subdomains it expects and a Caddyfile to adapt (`containers/stubs/proxy/runtimes/Caddyfile`):

```bash
docker compose \
  -f containers/docker/docker-compose.yml \
  -f containers/docker/docker-compose.proxy.yml \
  up -d
```

---

## Service Overview

Service names below match the keys in `containers/docker/docker-compose.yml` — containers are named `stry-{service}` by Compose (e.g. `stry-app`).

| Service     | Purpose                                | Port      |
| ----------- | -------------------------------------- | --------- |
| `app`       | Main application server (Octane)       | 8000      |
| `ssr`       | Server-side rendering (Node.js)        | 13714     |
| `queue`     | Background job processor (Horizon)     | —         |
| `reverb`    | WebSocket server                       | 6001      |
| `schedule`  | Task scheduler                         | —         |
| `pgsql`     | Database                               | 5432      |
| `redis`     | Cache & sessions                       | 6379      |
| `typesense` | Full-text search                       | 8108      |
| `rustfs`    | S3-compatible storage                  | 9000-9001 |
| `mailpit`   | Development email                      | 8025      |
| `proxy`     | Caddy reverse proxy (optional overlay) | 80, 443   |

---

## Common Commands

```bash
# Start / stop / restart
docker compose -f containers/docker/docker-compose.yml up -d
docker compose -f containers/docker/docker-compose.yml down
docker compose -f containers/docker/docker-compose.yml restart

# Logs
docker compose -f containers/docker/docker-compose.yml logs -f app

# Migrations / shell
docker exec stry-app php artisan migrate --force
docker exec -it stry-app /bin/bash
```

---

## Development Setup

Mount the application directory in `docker-compose.yml` for live code reloading:

```yaml
services:
  app:
    volumes:
      - ./:/app:rw
      - /app/vendor # Prevent vendor from mounting
      - /app/node_modules # Prevent node_modules from mounting
    environment:
      APP_ENV: local
      APP_DEBUG: 'true'
```

Then run Vite from the container:

```bash
docker exec -it stry-app pnpm dev
```

---

## GPU Acceleration

Uncomment the `devices` block for the `queue` service in `containers/docker/docker-compose.yml`:

```yaml
queue:
  devices:
    - /dev/dri:/dev/dri
```

> [!NOTE]
> Requires VAAPI (Intel), mesa (AMD), or NVENC (Nvidia) drivers on the host — see the [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html). GPU passthrough is more limited on Docker Desktop than on native Linux.

---

## Troubleshooting

- **Container won't start** — `docker compose -f containers/docker/docker-compose.yml logs app`
- **Permission denied** — `sudo chown -R $USER:$USER ~/projects/stry`
- **Port already in use** — change the host-side port under `ports:` for that service
- **Database connection failed** — check `containers/config/postgres.env` and `docker compose ... ps`

## Known limitations vs Podman

Docker Compose doesn't have Podman-specific features used by the Quadlet setup: `UserNS=keep-id` (rootless file ownership), `AutoUpdate`, and systemd-managed lifecycle/autostart. Manage services yourself accordingly.

---

## Next Steps

- Review **[Production Setup](production.md)** for the security checklist
- Set up **[Proxy Configuration](proxy.md)** for HTTPS
- Configure **[Object Storage (S3)](s3.md)** for media files
- Check **[Application Configuration](configuration.md)** for customization

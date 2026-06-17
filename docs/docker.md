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

```bash
# First, set up Caddy configuration:
mkdir -p containers/podman/systemd/proxy/config
cp containers/podman/systemd/proxy/config/Caddyfile.example containers/podman/systemd/proxy/config/Caddyfile
vi containers/podman/systemd/proxy/config/Caddyfile

# Then start with proxy overlay:
docker compose \
  -f containers/docker/docker-compose.yml \
  -f containers/docker/docker-compose.proxy.yml \
  up -d
```

> [!TIP]
> See the [Proxy Setup](proxy.md) guide for Caddyfile examples and domain configuration.

---

## Service Overview

| Service          | Purpose                            | Port      |
| ---------------- | ---------------------------------- | --------- |
| `stry`           | Main application server (Octane)   | 8000      |
| `stry-ssr`       | Server-side rendering (Node.js)    | 13714     |
| `stry-queue`     | Background job processor (Horizon) | —         |
| `stry-reverb`    | WebSocket server                   | 6001      |
| `stry-schedule`  | Task scheduler                     | —         |
| `stry-postgres`  | Database                           | 5432      |
| `stry-redis`     | Cache & sessions                   | 6379      |
| `stry-typesense` | Full-text search                   | 8108      |
| `stry-rustfs`    | S3-compatible storage              | 9000-9001 |
| `stry-mailpit`   | Development email                  | 8025      |
| `proxy`          | Caddy reverse proxy (optional)     | 80, 443   |

---

## Common Commands

```bash
# Start services
docker compose -f containers/docker/docker-compose.yml up -d

# Stop services
docker compose -f containers/docker/docker-compose.yml down

# View logs
docker compose -f containers/docker/docker-compose.yml logs -f stry

# Run migrations
docker exec stry php artisan migrate --force

# Access shell
docker exec -it stry /bin/bash

# Restart services
docker compose -f containers/docker/docker-compose.yml restart
```

---

## Development Setup

For local development with live code reloading:

**Step 1:** Mount the application directory in `docker-compose.yml`:

```yaml
services:
    stry:
        # ... existing config ...
        volumes:
            - ./:/app:rw
            - /app/vendor # Prevent vendor from mounting
            - /app/node_modules # Prevent node_modules from mounting
        environment:
            APP_ENV: local
            APP_DEBUG: 'true'
```

**Step 2:** Start Vite development server:

```bash
docker exec -it stry pnpm dev
```

---

## Resource Limits

Adjust resource limits in the compose file for your hardware:

```yaml
services:
    stry:
        deploy:
            resources:
                limits:
                    cpus: '4'
                    memory: 4G
                reservations:
                    cpus: '2'
                    memory: 2G
```

Recommended for production:

| Service         | CPU | Memory |
| --------------- | --- | ------ |
| `stry`          | 4-8 | 4-6GB  |
| `stry-queue`    | 4-6 | 6-8GB  |
| `stry-postgres` | 2-4 | 4-8GB  |
| `stry-redis`    | 1-2 | 1-2GB  |

---

## Troubleshooting

### Container Won't Start

```bash
docker compose -f containers/docker/docker-compose.yml logs stry
```

### Permission Denied Errors

```bash
# Fix volume ownership
sudo chown -R $USER:$USER ~/projects/stry
```

### Port Already in Use

```bash
# Change port in compose file
ports:
  - "8001:8000"  # Change external port
```

### Database Connection Failed

```bash
# Verify PostgreSQL is running and healthy
docker compose -f containers/docker/docker-compose.yml ps

# Check database credentials in app.env
docker compose -f containers/docker/docker-compose.yml exec stry-postgres psql -U user -d stry
```

---

## Known Limitations vs Podman

⚠️ **Docker Compose lacks these Podman features:**

- `UserNS=keep-id` — File ownership mapping in rootless containers
- `AutoUpdate` — Automatic image updates
- GPU support via `AddDevice` — Limited on Docker Desktop
- Systemd integration — Manual service management

---

## Next Steps

- Review **[Production Setup](production.md)** for security checklist
- Set up **[Proxy Configuration](proxy.md)** for HTTPS
- Configure **[Object Storage (S3)](s3.md)** for media files
- Check **[Application Configuration](configuration.md)** for customization

| Service              | Role                         | Default Port            |
| -------------------- | ---------------------------- | ----------------------- |
| `app`                | FrankenPHP / Octane (HTTP)   | 8000                    |
| `queue`              | Laravel Horizon worker       | —                       |
| `schedule`           | Laravel scheduler            | —                       |
| `reverb`             | Laravel Reverb WebSockets    | 6001                    |
| `ssr`                | Inertia.js SSR               | 13714                   |
| `redis`              | Valkey (Redis-compatible)    | 6379                    |
| `pgsql`              | PostgreSQL                   | 5432                    |
| `typesense`          | Typesense search             | 8108                    |
| `rustfs`             | S3-compatible object storage | 9000 / 9001             |
| `mailpit`            | Dev mail catcher             | 1025 (SMTP) / 8025 (UI) |
| `proxy` _(optional)_ | Caddy reverse proxy          | 80 / 443                |

---

## GPU Acceleration

To enable GPU access for video encoding in the `queue` service, uncomment the `devices` block in `containers/docker/docker-compose.yml`:

```yaml
queue:
    devices:
        - /dev/dri:/dev/dri
```

> [!NOTE]
> You may also need to install VAAPI drivers (Intel), mesa packages, or NVENC (Nvidia) dependencies on the host. See the [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html).

---

## Stopping & Cleanup

```bash
# Stop all services
docker compose -f containers/docker/docker-compose.yml down

# Stop and remove volumes (destructive!)
docker compose -f containers/docker/docker-compose.yml down -v
```

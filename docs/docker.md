---
title: Docker Compose
order: 9
tags:
    - docker
    - compose
    - containers
---

# 🐳 Docker Compose Setup

## Introduction

An alternative to the Podman/Quadlet setup is available for environments where Docker is preferred.
Two compose files are provided in `containers/docker/`:

| File                       | Purpose                                           |
| -------------------------- | ------------------------------------------------- |
| `docker-compose.yml`       | Core services, ports exposed directly on the host |
| `docker-compose.proxy.yml` | Override that adds a Caddy reverse proxy          |

> [!NOTE]
> The recommended setup for this project is [Podman Quadlet](podman.md). The Docker Compose files are a best-effort translation and may require adjustments for your environment.

---

## Prerequisites

- 🐳 [Docker Engine](https://docs.docker.com/engine/install/) or [Docker Desktop](https://docs.docker.com/desktop/)
- 🛠️ [Docker Compose v2](https://docs.docker.com/compose/install/) (included with Docker Desktop)
- 🔧 Basic tools: `git`, `bash`

> [!WARNING]
> Some Podman-specific features (rootless `UserNS=keep-id`, `AutoUpdate`, GPU `AddDevice`) have no direct Docker Compose equivalent. See the notes in the compose files.

---

## Setup

### Clone the Project

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
cd stry
```

### Build the Application Image

```bash
docker build -f containers/Containerfile -t stry.build .
```

### Create Environment Files

Create the config directory and populate each env file:

```bash
mkdir -p containers/config
```

Required files:

| File                              | Variables                                              |
| --------------------------------- | ------------------------------------------------------ |
| `containers/config/app.env`       | All Laravel application variables (see `.env.example`) |
| `containers/config/postgres.env`  | `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`    |
| `containers/config/typesense.env` | `TYPESENSE_DATA_DIR`, `TYPESENSE_API_KEY`              |
| `containers/config/rustfs.env`    | `RUSTFS_ACCESS_KEY`, `RUSTFS_SECRET_KEY`, etc.         |

---

## Usage

### Without a reverse proxy (direct port access)

```bash
docker compose -f containers/docker/docker-compose.yml up -d
```

The application will be available at **<http://localhost:8000>**.

### With Caddy reverse proxy

Place your `Caddyfile` at `containers/podman/systemd/proxy/config/Caddyfile` (the same location used by the Quadlet setup), then:

```bash
docker compose \
  -f containers/docker/docker-compose.yml \
  -f containers/docker/docker-compose.proxy.yml \
  up -d
```

> [!TIP]
> See the [Proxy Setup](proxy.md) guide for Caddyfile examples.

---

## Service Overview

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

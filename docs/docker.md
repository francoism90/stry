---
title: Docker Compose
sidebar_position: 6
tags:
    - docker
    - compose
    - containers
    - alternative
---

# Docker Compose Setup

Docker Compose is an **alternative** to the recommended Podman Quadlet setup. It can be a good fit if:

- you prefer Docker over Podman
- you need to run on macOS or Windows as well as Linux
- your infrastructure already runs on Docker

:::note
[Podman Quadlet](podman.md) is the recommended and tested setup. The Docker Compose files are maintained on a best-effort basis, so you may need to adjust them.
:::

## Prerequisites

- [Docker Engine](https://docs.docker.com/engine/install/) or [Docker Desktop](https://docs.docker.com/desktop/)
- [Docker Compose v2+](https://docs.docker.com/compose/install/) (included with Docker Desktop)
- `git` and `bash`

## Setup

### Clone the project

```bash
cd ~/projects
git clone https://github.com/francoism90/stry.git
cd stry
```

### Build the application image

Docker only reads `.dockerignore` from the root of the build context, so copy the template there first. Otherwise files such as your local `.env` or `vendor/` end up inside the image:

```bash
cp containers/stubs/frankenphp-octane/runtimes/dockerignore .dockerignore
docker build -f containers/stubs/frankenphp-octane/runtimes/Containerfile -t stry:latest .
```

### Create the environment files

Create the config folder:

```bash
mkdir -p containers/config
```

Then fill in these files:

| File                              | Contents                                                                 |
| --------------------------------- | ------------------------------------------------------------------------ |
| `containers/config/app.env`       | Application settings (see [Application Configuration](configuration.md)) |
| `containers/config/postgres.env`  | PostgreSQL credentials                                                   |
| `containers/config/typesense.env` | Typesense settings                                                       |
| `containers/config/rustfs.env`    | RustFS (S3) credentials                                                  |

Use `.env.example` as the starting point for `app.env`:

```bash
cp .env.example containers/config/app.env
vi containers/config/app.env
```

## Usage

```bash
docker compose -f containers/docker/docker-compose.yml up -d
```

The application is then available at `http://localhost:8000`.

For HTTPS and subdomains, put your own reverse proxy in front of port `8000`. You don't need an extra proxy service for Reverb, RustFS or Mailpit: the app's built-in Caddy server already forwards requests to them. See [Reverse Proxy](proxy.md).

## Services

The service names below match `containers/docker/docker-compose.yml`. Compose names the containers `stry-{service}`, for example `stry-app`.

| Service     | Purpose                          | Port      |
| ----------- | -------------------------------- | --------- |
| `app`       | Main application server (Octane) | 8000      |
| `ssr`       | Server-side rendering (Node.js)  | 13714     |
| `queue`     | Background jobs (Horizon)        | -         |
| `reverb`    | WebSocket server                 | 6001      |
| `schedule`  | Task scheduler                   | -         |
| `pgsql`     | Database                         | 5432      |
| `redis`     | Cache and sessions               | 6379      |
| `typesense` | Full-text search                 | 8108      |
| `rustfs`    | S3-compatible storage            | 9000-9001 |
| `mailpit`   | Catches mail during development  | 8025      |

## Common commands

```bash
# Start, stop and restart
docker compose -f containers/docker/docker-compose.yml up -d
docker compose -f containers/docker/docker-compose.yml down
docker compose -f containers/docker/docker-compose.yml restart

# Follow the app's logs
docker compose -f containers/docker/docker-compose.yml logs -f app

# Run migrations or open a shell
docker exec stry-app php artisan migrate --force
docker exec -it stry-app /bin/bash
```

## Development setup

To see code changes without rebuilding, mount the project folder in `docker-compose.yml`:

```yaml
services:
    app:
        volumes:
            - ./:/app:rw
            - /app/vendor # keep the container's own vendor folder
            - /app/node_modules # keep the container's own node_modules folder
        environment:
            APP_ENV: local
            APP_DEBUG: 'true'
```

Then run Vite inside the container:

```bash
docker exec -it stry-app pnpm dev
```

## GPU acceleration

Uncomment the `devices` block of the `queue` service in `containers/docker/docker-compose.yml`:

```yaml
queue:
    devices:
        - /dev/dri:/dev/dri
```

:::note
The host needs the right GPU drivers: VA-API (Intel), Mesa (AMD) or NVENC (Nvidia). See the [hardware encoding docs](https://shaka-project.github.io/shaka-streamer/hardware_encoding.html). GPU access is more limited on Docker Desktop than on Linux.
:::

## Troubleshooting

- **A container won't start**: check its logs with `docker compose -f containers/docker/docker-compose.yml logs app`.
- **Permission denied**: run `sudo chown -R $USER:$USER ~/projects/stry`.
- **A port is already in use**: change the host port under `ports:` for that service.
- **The database connection fails**: check `containers/config/postgres.env` and the output of `docker compose ... ps`.

## Differences from Podman

Docker Compose doesn't support some Podman features that the Quadlet setup relies on: `UserNS=keep-id` (correct file ownership in rootless containers), `AutoUpdate`, and starting and managing services through systemd. With Docker, you need to handle these yourself.

## Next steps

- Read the security checklist in **[Production Setup](production.md)**
- Set up a **[reverse proxy](proxy.md)** for HTTPS and subdomains
- Configure **[object storage (S3)](s3.md)** for media files
- See **[Application Configuration](configuration.md)** for everything you can customize

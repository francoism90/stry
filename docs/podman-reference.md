---
title: Podman Reference
order: 6
tags:
    - podman
    - quadlet
    - reference
    - resource-limits
    - security
---

# Podman Reference

## Resource Limits

Container memory and shared memory settings are tuned for a **~16 GB development machine** (12 GB total cap). Adjust based on your hardware:

| Container              | Memory | ShmSize | Notes                                             |
| ---------------------- | ------ | ------- | ------------------------------------------------- |
| `stry` (Octane)        | 2 GB   | 128 MB  | Scale with worker count; 4–6 GB in production     |
| `stry-queue` (Horizon) | 3 GB   | 256 MB  | FFmpeg needs ~1–2 GB; 6–8 GB in production        |
| `stry-pgsql`           | 2 GB   | 512 MB  | 4–8 GB in production                              |
| `stry-typesense`       | 1 GB   | default | Grows with index size; 2–4 GB in production       |
| `stry-rustfs`          | 1 GB   | default | 2–4 GB in production                              |
| `stry-redis`           | 512 MB | default | 1–2 GB in production                              |
| `stry-ssr`             | 512 MB | default | 1–2 GB in production                              |
| `stry-schedule`        | 512 MB | default | Lightweight; 512 MB–1 GB in production            |
| `stry-reverb`          | 512 MB | default | ~1 MB per 1,000 connections; 1–2 GB in production |
| `stry-mailpit`         | 256 MB | default | Dev only                                          |
| `proxy`                | 256 MB | default | 512 MB–1 GB in production                         |

"default" ShmSize means Podman default of **64 MB** (no explicit value set).

### Adjusting Resource Limits

Edit the container file and update `Memory=` or `ShmSize=`:

```bash
vi ~/.config/containers/systemd/stry/stry.container
```

Example:

```ini
[Container]
Memory=4gb
ShmSize=512m
```

Reload and restart:

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

### PostgreSQL Shared Memory

PostgreSQL's `shared_buffers` defaults to 128 MB regardless of container memory. For production, set it to ~25% of `Memory=`:

Option 1: Update the container Exec line:

```ini
Exec=postgres -c shared_buffers=1GB
```

Option 2: Mount a custom `postgresql.conf` and adjust in there.

### Redis Memory Policy

Valkey/Redis has `--maxmemory 480mb` default. If you increase `Memory=`, update `--maxmemory` to match (leave ~30 MB headroom):

```ini
Exec=valkey-server --maxmemory 1gb --maxmemory-policy allkeys-lru
```

### Out-of-Memory (OOM) Issues

If a container is OOM-killed during heavy workloads (indexing large libraries, processing many concurrent jobs):

1. Check dmesg: `dmesg | grep OOMkiller`
2. Increase `Memory=` for that container
3. Reload systemd and restart

---

## Security Hardening

All containers ship with hardening applied:

### `NoNewPrivileges=true`

Prevents setuid/setgid escalation after container start.

### `DropCapability=ALL`

Applied to all app-based containers (`stry`, `stry-queue`, `stry-reverb`, `stry-schedule`, `stry-ssr`). These run as mapped users via `UserNS=keep-id` and require no Linux capabilities.

### Proxy (Caddy)

Uses `DropCapability=ALL` plus:

```ini
AddCapability=CAP_NET_BIND_SERVICE
```

This allows only the capability needed to bind to ports < 1024.

---

## Environment Variables in Detail

### Application Configuration (app.env)

All values from `app.env` are copied into `/app/.env` at container startup. See [Podman Configuration](podman-configuration.md) for the full reference.

### Service Selection (APP_SERVICE)

Set automatically by Quadlet unit files. Controls which service the container runs:

- `app` — HTTP server
- `ssr` — Node.js SSR renderer
- `horizon` — Queue worker
- `reverb` — WebSocket server
- `scheduler` — Background scheduler

Example (in `stry-queue.container`):

```ini
Environment=APP_SERVICE=horizon
```

### Runtime Environment (APP_ENV)

Controls production-specific startup behavior (migrations, optimization, PWA generation). Set in `app.env`:

```env
APP_ENV=production
```

---

## Quadlet Unit Files

### File Types

- `.container` — Container service definitions
- `.build` — Image build specifications
- `.network` — Network definitions
- `.volume` — Named volume definitions
- `.kube` — Kubernetes manifest support (not used here)

### Service Dependencies

Example from `stry.container`:

```ini
[Unit]
After=stry-redis.container stry-pgsql.container stry-rustfs.container stry-typesense.container
Requires=stry-redis.container stry-pgsql.container stry-rustfs.container stry-typesense.container
Wants=stry-mailpit.container stry-reverb.container stry-queue.container stry-schedule.container stry-ssr.container
```

- `After` — services that must start first
- `Requires` — services that must succeed to start this one
- `Wants` — optional dependencies (won't fail if they fail)

---

## Network Configuration

Services are connected via two networks:

1. **`stry.network`** — Internal service network
2. **`proxy.network`** — Shared with reverse proxy (Caddy)

Example (in `stry.container`):

```ini
Network=stry.network
Network=proxy.network
```

---

## Volume Management

### Named Volumes

Created by `.volume` files; data persists independently of containers:

```bash
# List volumes
podman volume ls

# Inspect volume
podman volume inspect stry-pgsql

# Remove volume (DESTRUCTIVE)
podman volume rm stry-pgsql
```

### Bind Mounts

Host paths mounted into containers (e.g., `MEDIA_PATH`, `IMPORT_PATH`):

```bash
# In container unit file:
Volume=/home/user/data/stry/media:/media:rw,z,U
```

- `rw` — read-write
- `z` — shared SELinux context (can be expensive with large directories)
- `U` — auto-recursively chown to container user

---

## Debugging & Inspection

### Enter a Container Shell

```bash
podman exec -it systemd-stry /bin/bash
```

### Inspect Container Config

```bash
podman inspect systemd-stry
```

### View Mount Points

```bash
podman exec systemd-stry mount | grep -E "(media|import|cache)"
```

### Check Network

```bash
podman network inspect stry.network
```

---

## Performance Tuning

### I/O Optimization

For large media libraries on slow storage, consider:

- Disabling container logging: `LogDriver=none`
- Using `DropCapability=ALL` (already done)
- Pre-labeling SELinux contexts instead of relying on `:z` flag

### CPU Limits

To limit CPU usage per container, add to the `[Container]` section:

```ini
CPUQuota=75%
```

This limits the container to 75% of one CPU core.

### Memory Swappiness

To prefer using swap less aggressively:

```ini
MemorySwappiness=10
```

---

## Maintenance

### Prune Unused Resources

```bash
podman system prune
```

Removes unused images, containers, networks, and volumes.

> [!WARNING]
> This is destructive — it removes stopped containers and dangling images. Run only if you're sure.

### Update Base Images

To pull fresh base images (FrankenPHP, PostgreSQL, etc.):

```bash
podman pull docker.io/dunglas/frankenphp:latest
podman pull docker.io/library/postgres:latest
podman pull docker.io/valkey/valkey:latest

systemctl --user restart stry-build
systemctl --user restart stry
```

---

## Further Reading

- [Podman Documentation](https://docs.podman.io/)
- [Systemd Unit Files](https://www.freedesktop.org/software/systemd/man/latest/systemd.unit.html)
- [Quadlet Documentation](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html)

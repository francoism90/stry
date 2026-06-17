---
title: Podman Operations
order: 5
tags:
    - podman
    - quadlet
    - operations
    - management
---

# Podman Operations

## Starting Containers

### First-Time Start

> [!WARNING]
> First start can take several minutes as it:
>
> - Runs database migrations (`artisan migrate`)
> - Runs Laravel optimization (`artisan optimize`)
> - Creates storage symlink (`artisan storage:link`)
> - Generates PWA assets (`artisan pwa:generate`)
> - Syncs search indexes (`artisan scout:sync`)
>
> **Do not cancel!** Increase `TimeoutStartSec=` in container files if needed.

To rebuild and start all containers:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

Monitor startup progress:

```bash
journalctl --user -u stry -f
```

---

## Managing Containers

### Basic Commands

```bash
# Start all services
systemctl --user start stry

# Stop all services
systemctl --user stop stry

# Restart all services
systemctl --user restart stry

# Check status
systemctl --user status stry
```

### Individual Container Control

```bash
# Start specific container
systemctl --user start stry-queue

# Restart a container
systemctl --user restart stry-reverb

# Check container status
systemctl --user status stry-pgsql
```

### Viewing Logs

```bash
# All stry services
journalctl --user -u 'stry*' -f

# Specific service
journalctl --user -u stry-queue -f

# Using podman directly
podman logs -f systemd-stry
```

---

## Autostart on Boot

By default, containers autostart on boot via the `[Install]` section in unit files. To disable autostart for a service:

Remove or edit the `[Install]` section in the container unit file, then reload:

```bash
systemctl --user daemon-reload
```

---

## Upgrading

### Update Application Code

```bash
cd ~/projects/stry
git pull origin main
```

### Rebuild and Restart

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

**When to rebuild:**

- After pulling code changes from git
- After updating `composer.json` or `package.json`
- After modifying PHP extensions or container configuration
- After updating base images (FrankenPHP, PostgreSQL, etc.)

**Simple restarts** do not rebuild the image.

### Update System Services

If you modified Quadlet unit files (`.container`, `.network`, `.volume`):

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

### Database Migrations

After rebuilding, run any pending migrations:

```bash
podman exec systemd-stry php artisan migrate --force
```

---

## Container Roles (APP_SERVICE)

All containers use the same image but run different "services" via the `APP_SERVICE` environment variable:

| Service     | Container       | Purpose                          |
| ----------- | --------------- | -------------------------------- |
| `app`       | `stry`          | HTTP application server (Octane) |
| `ssr`       | `stry-ssr`      | Server-side rendering (Node.js)  |
| `horizon`   | `stry-queue`    | Queue worker + job processor     |
| `reverb`    | `stry-reverb`   | WebSocket server                 |
| `scheduler` | `stry-schedule` | Background task scheduler        |

Each container receives its `APP_SERVICE` via the `Environment=APP_SERVICE=...` line in its unit file.

---

## Database Operations

### Run Migrations

```bash
podman exec systemd-stry php artisan migrate
```

### Seed the Database

```bash
podman exec systemd-stry php artisan db:seed
```

### Fresh Database

```bash
podman exec systemd-stry php artisan migrate:fresh --seed
```

### Backup PostgreSQL

```bash
podman exec systemd-stry-pgsql pg_dump -U user -d stry > stry-backup.sql
```

---

## Queue & Scheduler

### View Queue Jobs

```bash
podman exec systemd-stry php artisan queue:list
```

### Monitor Horizon Dashboard

Access at `http://localhost/horizon` (or your configured app URL).

### Manual Task Trigger

```bash
podman exec systemd-stry php artisan schedule:run
```

---

## Search Index Management

### Sync Scout Indexes

```bash
podman exec systemd-stry php artisan scout:sync
```

### Flush Scout Indexes

```bash
podman exec systemd-stry php artisan scout:flush
```

### Index Specific Model

```bash
podman exec systemd-stry php artisan scout:import "App\\Models\\Video"
```

---

## Cache & Optimization

### Clear All Caches

```bash
podman exec systemd-stry php artisan optimize:clear
```

### Cache Configuration

```bash
podman exec systemd-stry php artisan config:cache
```

### Cache Routes

```bash
podman exec systemd-stry php artisan route:cache
```

---

## Troubleshooting

### Container Won't Start

Check logs:

```bash
journalctl --user -u stry -f
```

Look for:

- Missing `app.env` file
- Missing `APP_KEY` in runtime configuration
- Permission issues on bind-mounted paths
- Port conflicts with existing services

### Permission Issues

Verify directory ownership:

```bash
ls -l /home/user/data/stry/media
# Should show: drwx------ 1000:1000
```

Fix if needed:

```bash
chown -R 1000:1000 /home/user/data/stry
chmod -R 700 /home/user/data/stry
```

### Timeout Errors

Increase `TimeoutStartSec=` in the container file if first-time setup is taking too long.

### Rebuild Needed

If you're unsure whether a rebuild is needed, it's safe to do:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

---

## Reference

For resource limits, security hardening, and advanced configuration, see [Podman Reference](podman-reference.md).

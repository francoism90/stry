---
title: Production Setup
order: 1
tags:
    - production
    - deployment
    - security
---

# Production Setup

## Overview

This guide covers deploying **stry** to production using Podman/Quadlet on Linux servers.

**Estimated time:** 30-60 minutes for initial setup + ongoing monitoring.

---

## Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support
- 🔐 Root access or sudo privileges for system configuration
- 📡 Public IP address or domain name
- 💾 Sufficient disk space for media files and databases

---

## Step-by-Step Setup

### 1. Clone the Project

```bash
cd ~/projects
git clone https://github.com/francoism90/stry.git
cd stry
```

### 2. Configure Containers

Follow the **[Podman Quick Start](podman-quickstart.md)** guide to set up basic container infrastructure:

```bash
mkdir -p ~/.config/containers/systemd
cp -r containers/podman/systemd/stry ~/.config/containers/systemd/
```

Edit `~/.config/containers/systemd/stry/config/app.env` with your production values:

```env
APP_NAME=stry
APP_ENV=production          # Critical: must be 'production'
APP_DEBUG=false
APP_KEY=base64:...          # Generate with: php artisan key:generate --show
APP_URL=https://stry.example.com

# Database
DB_HOST=systemd-stry-pgsql
DB_DATABASE=stry
DB_USERNAME=stry_user
DB_PASSWORD=<strong-password>

# Redis/Cache
REDIS_HOST=systemd-stry-redis

# S3 Storage
AWS_ENDPOINT_URL=https://fs.stry.example.com
AWS_ACCESS_KEY_ID=<access-key>
AWS_SECRET_ACCESS_KEY=<secret-key>

# Local paths (create these on host)
APP_PATH=/home/user/projects/stry
MEDIA_PATH=/mnt/stry/media
IMPORT_PATH=/mnt/stry/import
```

# Additional config (see config/ directory for all options)

PLAYLIST_TYPE=packager
PLAYLIST_ENCRYPTION=raw_key_encryption

````

### 3. Set Up Reverse Proxy

A **Caddy** reverse proxy is required for HTTPS termination and service routing.

Follow the **[Proxy Setup](proxy.md)** guide:

```bash
cp -r containers/podman/systemd/proxy ~/.config/containers/systemd/
````

Create `~/.config/containers/systemd/proxy/config/Caddyfile` with your domain and certificate configuration.

### 4. Create Media Directories

```bash
sudo mkdir -p /var/lib/stry/media /var/lib/stry/import
sudo chown 1000:1000 /var/lib/stry/media /var/lib/stry/import
sudo chmod 700 /var/lib/stry/media /var/lib/stry/import
```

### 5. Configure S3 Storage

Follow the **[Object Storage (S3)](s3.md)** setup guide to initialize buckets and configure media storage:

```bash
podman exec systemd-stry php artisan podman:s3-setup
```

### 6. Start Services

```bash
systemctl --user daemon-reload
systemctl --user start stry
```

Monitor startup (takes 5-10 minutes first time):

```bash
journalctl --user -u stry -f
```

### 7. Run Database Migrations

```bash
podman exec systemd-stry php artisan migrate --force
```

### 8. Verify Deployment

```bash
# Check status
systemctl --user status stry

# Test endpoints
curl -I https://stry.example.com/

# View logs
journalctl --user -u 'stry*' -f
```

---

## 🔒 Security Hardening

> [!IMPORTANT]
> **Production Security Checklist:**

- ✅ **Strong Passwords** — Use `openssl rand -hex 32` to generate strong secrets

    ```bash
    # Generate for APP_KEY
    php artisan key:generate --show

    # Generate for S3 credentials
    openssl rand -hex 16  # Access key
    openssl rand -hex 32  # Secret key
    ```

- ✅ **HTTPS/SSL** — Enable automatic HTTPS with Caddy (included in proxy setup)

- ✅ **Firewall** — Restrict access to essential ports only

    ```bash
    sudo ufw allow 22/tcp       # SSH
    sudo ufw allow 80/tcp       # HTTP (Caddy redirect)
    sudo ufw allow 443/tcp      # HTTPS (Caddy)
    sudo ufw enable
    ```

- ✅ **Database** — Use strong passwords and restrict network access

    ```bash
    # PostgreSQL credentials in app.env
    DB_PASSWORD=<use-strong-password>
    ```

- ✅ **Secrets Management** — Never commit credentials to git

    ```bash
    # app.env should NOT be in version control
    # Only .env.example with placeholders
    ```

- ✅ **Container Updates** — Keep Podman and containers updated

    ```bash
    sudo apt update && sudo apt upgrade podman
    systemctl --user restart stry-build
    systemctl --user restart stry
    ```

- ✅ **Automated Backups** — Schedule regular database backups

    ```bash
    # Cron job example (daily at 2 AM)
    0 2 * * * podman exec systemd-stry-pgsql pg_dump -U stry_user -d stry | gzip > /backups/stry-$(date +\%Y\%m\%d).sql.gz
    ```

- ✅ **Monitoring & Alerts** — Set up log monitoring

    ```bash
    # Real-time monitoring
    journalctl --user -u 'stry*' -f

    # Check for errors
    journalctl --user -u 'stry*' --priority=err
    ```

- ✅ **No Development Data** — Clean databases before production
    ```bash
    # Verify app.env has APP_ENV=production
    # Never run seeders on production data
    ```

---

## ⚡ Performance Optimization

### Resource Allocation

See **[System Configuration](system.md)** for detailed resource tuning. Key production guidelines:

| Container       | Memory | Notes                           |
| --------------- | ------ | ------------------------------- |
| `stry` (Octane) | 4-6 GB | Scale with worker count         |
| `stry-queue`    | 6-8 GB | FFmpeg encoding needs resources |
| `stry-pgsql`    | 4-8 GB | Increase for large datasets     |
| `stry-redis`    | 1-2 GB | Cache management                |

Update container files:

```ini
[Container]
Memory=6gb
CPUQuota=400000  # 4 cores
```

Reload and restart:

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

### Container Logging

Disable container logging for high-throughput services to reduce I/O overhead:

```ini
[Container]
LogDriver=none
```

> [!NOTE]
> Application logs remain available through Laravel's logging system in `storage/logs/`.

### Video Processing

For optimal encoding performance:

- Allocate 6-8 GB memory to `stry-queue` container
- Configure job timeouts in `config/queue.php`
- Monitor FFmpeg CPU usage: `podman stats systemd-stry-queue`
- Use hardware acceleration (`AddDevice=/dev/dri:/dev/dri/`) if available

### Database Optimization

```sql
-- PostgreSQL performance tuning
-- In postgresql.conf or via podman exec:
shared_buffers = 2GB        # ~25% of container memory
effective_cache_size = 6GB  # ~75% of container memory
work_mem = 50MB
maintenance_work_mem = 512MB
```

---

## 📊 Monitoring & Maintenance

### Daily Checks

```bash
# Container status
systemctl --user status stry

# Real-time stats
podman stats

# Recent errors
journalctl --user -u 'stry*' --priority=err | tail -50
```

### Regular Maintenance

```bash
# Weekly: Check disk usage
du -sh ~/.config/containers/systemd/stry/

# Monthly: Prune old images and containers
podman system prune -a

# Quarterly: Update base images and dependencies
git pull origin main
systemctl --user restart stry-build
systemctl --user restart stry
```

### Backup Strategy

1. **Database** — Automated daily backups to external storage
2. **Media** — Replicate S3 bucket to backup location
3. **Configuration** — Version control app.env changes (in private repo)

---

## 🆘 Troubleshooting

**Container Won't Start**

```bash
journalctl --user -u stry -f  # Check logs
# Common issues: missing app.env, missing APP_KEY, port conflicts
```

**High Memory Usage**

```bash
podman stats                   # Identify heavy containers
systemctl --user restart stry  # Restart to reset memory
# Increase Memory= in container files if persistent
```

**Performance Issues**

```bash
podman exec systemd-stry php artisan optimize          # Cache config/routes
podman exec systemd-stry php artisan scout:sync        # Re-index search
systemctl --user restart stry-queue                    # Restart workers
```

---

## 📖 Next Steps

1. Review **[Application Configuration](configuration.md)** for app-specific settings
2. Set up **[CLI Interaction](interaction.md)** for easy command execution
3. Schedule **[automated upgrades](podman-operations.md#upgrading)**
4. Monitor using **[System Configuration](system.md)** guidelines
5. 🎮 Learn about [Interaction](interaction.md) commands
6. 📈 Monitor your instance with [Laravel Horizon](https://stry.test/horizon)

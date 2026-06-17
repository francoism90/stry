---
title: Podman Configuration
order: 4
tags:
    - podman
    - quadlet
    - configuration
    - selinux
---

# Podman Configuration

## Overview

Podman Quadlet provides systemd integration for managing containers. Learn more:

- [Podman Systemd Unit Documentation](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html)
- [Red Hat Quadlet Guide](https://www.redhat.com/sysadmin/quadlet-podman)
- [Practical Quadlet Tutorial](https://mo8it.com/blog/quadlet/)

---

## Runtime Environment (`app.env`)

The `app.env` file serves as the full Laravel `.env` configuration for the application at runtime. It is mounted into all app-role containers at `/config/app.env` and copied to `/app/.env` on startup.

### Required Configuration

| Key           | Purpose                                | Example                       |
| ------------- | -------------------------------------- | ----------------------------- |
| `APP_NAME`    | Application name                       | `stry`                        |
| `APP_ENV`     | Environment (production, local)        | `production`                  |
| `APP_KEY`     | Encryption key (generate with artisan) | `base64:...`                  |
| `APP_URL`     | Public application URL                 | `https://stry.example.com`    |
| `DB_HOST`     | PostgreSQL hostname                    | `systemd-stry-pgsql`          |
| `DB_DATABASE` | Database name                          | `stry`                        |
| `DB_USERNAME` | Database user                          | `user`                        |
| `DB_PASSWORD` | Database password                      | (secure value)                |
| `REDIS_HOST`  | Redis/Valkey hostname                  | `systemd-stry-redis`          |
| `MEDIA_PATH`  | Host path bind-mounted to `/media`     | `/home/user/data/stry/media`  |
| `IMPORT_PATH` | Host path bind-mounted to `/import`    | `/home/user/data/stry/import` |

### Optional Configuration

| Key          | Purpose                                  | Notes                                               |
| ------------ | ---------------------------------------- | --------------------------------------------------- |
| `UID`, `GID` | Custom user/group IDs for container proc | Only set if you need non-default IDs (default 1000) |

### APP_KEY Generation

Generate a fresh key for production deployments:

```bash
cd ~/projects/stry
php artisan key:generate --show
# Copy the output (e.g., base64:...) into app.env APP_KEY
```

---

## Media & Import Paths

Create the directories and ensure they have correct ownership:

```bash
mkdir -p /home/user/data/stry/media
mkdir -p /home/user/data/stry/import
chown -R 1000:1000 /home/user/data/stry
```

Update `app.env` to match:

```env
MEDIA_PATH=/home/user/data/stry/media
IMPORT_PATH=/home/user/data/stry/import
```

> [!NOTE]
> The `/cache` directory is managed by the `stry-cache` named Podman volume — no host path needed.

---

## SELinux & Volume Flags

If using SELinux (e.g. Fedora CoreOS), bind-mounted volumes need the `container_file_t` context.

### Recommended: Pre-label Once

Instead of relying on `:z` or `:Z` volume flags (which relabel on every start), pre-label once:

```bash
sudo semanage fcontext -a -t container_file_t "${MEDIA_PATH}(/.*)?"
sudo restorecon -Rv "${MEDIA_PATH}"
sudo semanage fcontext -a -t container_file_t "${IMPORT_PATH}(/.*)?"
sudo restorecon -Rv "${IMPORT_PATH}"
```

After this, Podman skips relabeling entirely on subsequent starts.

### ZFS / Symlinked Mount Points

If using ZFS on CoreOS (storage under `/var/mnt/...`), use the **canonical path** for `semanage` and **real path** for `restorecon`:

```bash
# Use /mnt/... for semanage (canonical path through SELinux equivalency)
sudo semanage fcontext -a -t container_file_t "/mnt/data/media(/.*)?"
# Use /var/mnt/... for restorecon (actual path on disk)
sudo restorecon -Rv /var/mnt/data/media
```

### File Ownership & Context

- **New files** created inside the container or copied on the host automatically inherit `container_file_t`
- **Moved files** preserve their original context — run `restorecon -Rv` afterwards if needed

---

## Exposed Ports

| Service      | Port      |
| ------------ | --------- |
| App (HTTP)   | 8000      |
| Vite (Dev)   | 5173      |
| Reverb (WS)  | 6001      |
| SSR Renderer | 13714     |
| Mailpit SMTP | 1025      |
| Mailpit UI   | 8025      |
| PostgreSQL   | 5432      |
| Redis        | 6379      |
| Typesense    | 8108      |
| RustFS       | 9000-9001 |

Ensure these are free on the host or adjust `ExposeHostPort` in container files.

---

## Hardware Acceleration

By default, hardware acceleration is supported via VAAPI (Intel), mesa (AMD), or NVENC (Nvidia). To disable:

Remove or comment out this line in `stry-queue.container`:

```podman
AddDevice=/dev/dri/:/dev/dri/
GroupAdd=keep-groups
```

---

## Container Logging

For production, disable logging to reduce disk overhead:

Add `LogDriver=none` to container files (e.g., `stry.container`):

```ini
[Container]
LogDriver=none
```

> [!NOTE]
> Application logs remain available through Laravel's logging system. Only systemd journal logs are affected.

---

## Rebuilding vs Restarting

**Restart** (does not rebuild image):

```bash
systemctl --user restart stry
```

**Rebuild** (needed when code or dependencies change):

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

**Reload systemd** (needed when modifying unit files):

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

---

## S3 Object Storage

Follow the [S3 Object Storage](s3.md) setup guide.

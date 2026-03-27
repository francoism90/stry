---
title: Podman Quadlet
order: 3
tags:
    - podman
    - quadlet
    - containers
    - systemd
    - compose
---

# 🐳 Podman Quadlet Setup

## Introduction

Podman Quadlet provides systemd integration for managing containers. Learn more:

- 📖 [Podman Systemd Unit Documentation](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html)
- 🔴 [Red Hat Quadlet Guide](https://www.redhat.com/sysadmin/quadlet-podman)
- 💡 [Practical Quadlet Tutorial](https://mo8it.com/blog/quadlet/)

---

## Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support

### Rootless Setup

This guide assumes a **rootless** Podman setup (recommended for security):

- 📖 [Rootless Podman Tutorial](https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md)
- 🔧 [Arch Linux Podman Guide](https://wiki.archlinux.org/title/Podman#Rootless_Podman)

> [!TIP]
> Your distribution may have already configured rootless Podman for you.

> [!NOTE]
> To keep the containers running when the user logs out, ensure you have lingering enabled for your user:

```bash
sudo loginctl enable-linger $USER
```

#### GPU Acceleration

If you are using rootless Podman and want GPU acceleration with SELinux enabled:

```bash
sudo setsebool -P container_use_dri_devices 1
```

Make sure your user is in the `render` and `video` groups to access GPU devices:

```bash
sudo usermod -aG render,video $USER
sudo reboot
```

---

## 🛠️ Installation

### Configure Container Files

Copy the container configuration files to your systemd user directory:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/podman/systemd/stry ~/.config/containers/systemd/
```

> [!TIP]
> **Alternative (Podman 5.2+):**
>
> You can use `podman quadlet install` for individual unit files:
>
> ```bash
> podman quadlet install ~/projects/stry/containers/podman/systemd/stry/*.{container,build,network,volume}
> ```
>
> However, `cp -r` is recommended as it preserves the `config/` subdirectory structure with environment files.

### Adjust Container Configuration

Edit the configuration files to match your environment:

```bash
cd ~/.config/containers/systemd/stry/config
vi app.env postgres.env typesense.env
```

> [!IMPORTANT]
> Ensure your project's `.env` file is aligned with the container configurations:

```bash
cp ~/projects/stry/.env.example ~/projects/stry/.env
vi ~/projects/stry/.env
```

### Hardware Acceleration (Optional)

By default, hardware acceleration is supported via VAAPI (Intel), mesa (AMD), or NVENC (Nvidia) drivers. If you do **not** want to use hardware acceleration, you may opt out:

- Remove or comment out the following line in your `stry-queue.container` (and any other relevant container files):

    ```podman
    AddDevice=/dev/dri/:/dev/dri/
    GroupAdd=keep-groups
    ```

This will prevent the container from accessing GPU devices for video encoding/decoding. Software encoding will be used instead.

> [!NOTE]
> Hardware acceleration is optional. If you encounter issues or do not require it, you can safely disable it as described above.

### Setup Storage Paths

Create the required data directories as defined in `app.env`:

**Single Disk (using `STORAGE_PATH`):**

```bash
mkdir -p /home/user/data/stry/{media,cache,import}
# Then set STORAGE_PATH=/home/user/data/stry
```

**Multiple Disks (individual paths):**

```bash
mkdir -p /mnt/disk1/media /mnt/disk2/cache /mnt/disk3/import
# Then set MEDIA_PATH, CACHE_PATH, and IMPORT_PATH separately
```

### SELinux & Volume Flags (if applicable)

If using SELinux (e.g. Fedora CoreOS), Podman needs files in bind-mounted volumes to carry the `container_file_t` context so the container process can access them.

**Named Podman volumes** (e.g. `stry-pgsql`, `stry-redis`) start empty so carrying `:Z` there is fine — Podman labels a small directory once.

**Large host bind mounts** (e.g. `${STORAGE_PATH}`) are a different story. Using `:z` or `:Z` causes Podman to recursively relabel every file on every container start, which can take a very long time for large media libraries.

#### Recommended: pre-label the directory once

Instead of `:z`, set the SELinux context permanently on the host once:

```bash
sudo semanage fcontext -a -t container_file_t "${STORAGE_PATH}(/.*)?"
sudo restorecon -Rv "${STORAGE_PATH}"
```

Replace `${STORAGE_PATH}` with the actual path from your `app.env`. After this, Podman sees the files are already correctly labeled and skips relabeling entirely on every subsequent start.

> [!WARNING]
> **ZFS / symlinked mount points (e.g. Fedora CoreOS):**
>
> If your storage is under `/var/mnt/...` (common with ZFS on CoreOS), SELinux has an equivalency rule mapping `/var/mnt → /mnt`. The `semanage fcontext` command must use the **canonical form** (`/mnt/...`), while `restorecon` uses the **real path** (`/var/mnt/...`):
>
> ```bash
> # Use /mnt/... for semanage (canonical path through equivalency)
> sudo semanage fcontext -a -t container_file_t "/mnt/data/media(/.*)?"
> # Use /var/mnt/... for restorecon (actual path on disk)
> sudo restorecon -Rv /var/mnt/data/media
> ```
>
> Using the real path in `semanage fcontext` will produce:
> `ValueError: File spec … conflicts with equivalency rule '/var/mnt /mnt'`

> [!NOTE]
> **New files** created inside the container or copied (`cp`) on the host automatically inherit `container_file_t` from the parent directory. Files **moved** (`mv`) onto the host preserve their original context — run `restorecon -Rv ${STORAGE_PATH}` afterwards if you do this.

> [!TIP]
> `:z` (shared) vs `:Z` (private) are still valid options if you prefer the simpler setup and your library is small enough that the relabeling delay is acceptable.

### Environment Variables (`app.env`)

| Key             | Purpose                                                   |
| --------------- | --------------------------------------------------------- |
| `UID`, `GID`    | Mapped user/group IDs for rootless container processes    |
| `CONTAINER_ENV` | Application environment (e.g. production)                 |
| `APP_PATH`      | Host path of source checkout (bind if enabling live code) |
| `STORAGE_PATH`  | Base host data directory (used if individual paths unset) |
| `MEDIA_PATH`    | Host data directory containing media subfolders           |
| `CACHE_PATH`    | Host data directory for cache storage                     |
| `IMPORT_PATH`   | Host data directory containing files to be imported       |

**Storage Path Strategy:**

You can use either approach:

- **Single Disk**: Set `STORAGE_PATH` and omit individual paths—media, cache, and import will use subdirectories under `STORAGE_PATH`
- **Multiple Disks**: Define `MEDIA_PATH`, `CACHE_PATH`, and `IMPORT_PATH` individually to use different storage locations per container

> > [!IMPORTANT]
> > **For Multiple Disks:** If using individual paths, you must also update your `.container` files to replace `${STORAGE_PATH}` with the individual variables (e.g., `${MEDIA_PATH}`, `${CACHE_PATH}`, `${IMPORT_PATH}`) in the `Volume=` directives. For example, change `Volume=${STORAGE_PATH}/media:/storage/media:rw,z,U` to `Volume=${MEDIA_PATH}:/storage/media:rw,z,U`.

Ensure paths exist and are owned by the matching UID/GID:

```bash
chown -R 1000:1000 /home/user/projects/stry /home/user/data/stry
```

### Exposed Ports

Primary application & services expose:

| Service      | Port      |
| ------------ | --------- |
| App (HTTP)   | 8080      |
| Vite (Dev)   | 5173      |
| Reverb (WS)  | 6001      |
| SSR Renderer | 13714     |
| Mailpit SMTP | 1025      |
| Mailpit UI   | 8025      |
| PostgreSQL   | 5432      |
| Redis        | 6379      |
| Typesense    | 8108      |
| RustFS       | 9000-9001 |

Ensure these are free on the host or adjust the `ExposeHostPort` lines in the respective container unit files.

### Container Logging Configuration

**For production deployments**, consider disabling container logging to reduce overhead:

Add `LogDriver=none` to your container files (e.g., `~/.config/containers/systemd/stry/stry.container`):

```ini
[Container]
LogDriver=none
```

> [!NOTE]
> **Performance Impact:**
>
> - ✅ Reduces disk I/O and CPU overhead from logging
> - ✅ Prevents log files from consuming disk space
> - ⚠️ Container logs won't be available via `journalctl` or `podman logs`
> - ℹ️ Application logs remain accessible through Laravel's logging system
>
> For development, keep logging enabled. For production, selectively disable it on high-throughput containers (queue workers, websockets) or all containers if using centralized application logging.

### Rebuilding vs Restarting

Restarting a container (`systemctl --user restart stry`) does not rebuild the image. Rebuild when dependencies, PHP extensions, or application code (without an `/app` bind) change:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

If you added or changed Quadlet unit files:

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

### Apply Configuration Changes

Reload systemd to recognize the new containers:

```bash
systemctl --user daemon-reload
```

### Configure S3 Storage

Follow the [S3 Object Storage](s3.md) setup guide.

---

## 🚀 Starting Containers

### First-Time Start

> [!WARNING]
> **First Start Can Take Time:**
>
> The initial startup can take several minutes as it:
>
> - Runs Laravel optimization (`artisan optimize`)
> - Creates storage symlinks (`artisan storage:link`)
>
> **Do not cancel this process!** If needed, increase the `TimeoutStartSec=*` value in your container files.

> [!TIP]
> If startup is slow on SELinux systems (e.g. Fedora CoreOS), pre-label your storage directory once instead of relying on the `:z` volume flag — see [SELinux & Volume Flags](#selinux--volume-flags-if-applicable) above.

To rebuild the image (if needed) and start all containers:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

### Managing Containers

```bash
# Start services
systemctl --user start stry

# Stop services
systemctl --user stop stry

# Check status
systemctl --user status stry

# View logs
journalctl --user -u stry -f
```

---

## 🔄 Upgrading

### Update Application Code

Pull the latest changes from the repository:

```bash
cd ~/projects/stry
git pull origin main
```

### Rebuild and Restart

Rebuild the application image and restart all containers:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

> [!NOTE]
> **When to rebuild:**
>
> - After pulling code changes from git
> - After updating dependencies (composer.json or package.json)
> - After modifying PHP extensions or container configuration
> - After updating base images (FrankenPHP, PostgreSQL, etc.)
>
> **Simple container restarts** (`systemctl --user restart stry`) do **not** rebuild the image.

### Update System Services

If you modified Quadlet unit files (`.container`, `.network`, `.volume`, etc.):

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

## 🔧 Container Management

### Individual Container Control

```bash
# Start specific container
systemctl --user start stry-queue

# Restart a container
systemctl --user restart stry-reverb

# Check container status
systemctl --user status stry-postgres
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

### Autostart on Boot

By default, Podman Quadlet containers will autostart on boot if the unit file contains an [Install] section with:

```ini
[Install]
WantedBy=multi-user.target default.target
```

If you do not want a container to start automatically on boot, you can remove the [Install] section from its unit file. The service will then only start when manually started with systemctl or podman systemd.

---

## 🧠 Resource Limits

Each container file ships with `Memory=` and `ShmSize=` limits tuned for a **~16 GB development machine** (12 GB total cap across all services). The current defaults are:

| Container                             | Dev Memory | Dev ShmSize | Prod Memory | Notes                                   |
| ------------------------------------- | ---------- | ----------- | ----------- | --------------------------------------- |
| `stry` (main app / Octane)            | 2 GB       | 128 MB      | 4–6 GB      | Scale with Octane worker count          |
| `stry-queue` (Horizon + FFmpeg)       | 3 GB       | 256 MB      | 6–8 GB      | One concurrent FFmpeg job needs ~1–2 GB |
| `stry-pgsql` (PostgreSQL)             | 2 GB       | 512 MB      | 4–8 GB      | —                                       |
| `stry-typesense` (Typesense search)   | 1 GB       | default     | 2–4 GB      | Grows with index size                   |
| `stry-rustfs` (RustFS object storage) | 1 GB       | default     | 2–4 GB      | More concurrent S3 operations           |
| `stry-redis` (Valkey cache)           | 512 MB     | default     | 1–2 GB      | Update `--maxmemory` to match           |
| `stry-ssr` (Node.js SSR)              | 512 MB     | default     | 1–2 GB      | More concurrent SSR renders             |
| `stry-schedule` (Laravel scheduler)   | 512 MB     | default     | 512 MB–1 GB | Stays lightweight unless jobs are heavy |
| `stry-reverb` (WebSocket server)      | 512 MB     | default     | 1–2 GB      | ~1 MB per 1 000 concurrent connections  |
| `stry-mailpit` (dev mail)             | 256 MB     | default     | —           | Dev only; not used in production        |
| `proxy` (Caddy reverse proxy)         | 256 MB     | default     | 512 MB–1 GB | Real traffic + TLS session cache        |

> "default" ShmSize means the Podman default of **64 MB** applies (no explicit value set).

> [!IMPORTANT]
> If your machine has **more or less RAM**, or if a container is OOM-killed during heavy workloads
> (e.g. indexing large video libraries, running many queue workers), adjust or remove the `Memory=`
> and `ShmSize=` lines in the relevant `.container` file and reload the daemon:
>
> ```bash
> systemctl --user daemon-reload
> systemctl --user restart stry
> ```

> [!NOTE]
> Valkey/Redis also has a matching in-process `--maxmemory 480mb` directive so it evicts old cache
> entries before hitting the container hard limit. If you raise `Memory=` for `stry-redis`, update
> the `--maxmemory` value in `Exec=` to match (leave ~30 MB headroom).

> [!NOTE]
> PostgreSQL's internal page cache (`shared_buffers`) defaults to `128MB` regardless of the container
> memory limit — it does not auto-scale. For production, set it to ~25% of `Memory=` either via
> `Exec=postgres -c shared_buffers=1GB` in `stry-pgsql.container` or by mounting a custom
> `postgresql.conf`.

### Security Hardening

All containers ship with the following hardening applied:

- **`NoNewPrivileges=true`** — prevents setuid/setgid escalation after container start.
- **`DropCapability=ALL`** — applied to all `stry.build`-based containers (`stry`, `stry-queue`, `stry-reverb`, `stry-schedule`, `stry-ssr`), which run as a mapped user via `UserNS=keep-id` and need no Linux capabilities.
- **`proxy` (Caddy)** — uses `DropCapability=ALL` combined with `AddCapability=CAP_NET_BIND_SERVICE` so only the port-binding capability is granted.

---

## 💡 Troubleshooting

> [!TIP]
> **Common Issues:**
>
> - **Container won't start**: Check `journalctl --user -u stry` for errors
> - **Permission issues**: Verify SELinux contexts and directory ownership
> - **Timeout errors**: Increase timeout values in container files
> - **Port conflicts**: Ensure no other services are using the same ports
> - **Rebuild needed**: See the [Upgrading](#-upgrading) section above

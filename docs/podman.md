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

## 📚 Introduction

Podman Quadlet provides systemd integration for managing containers. Learn more:

- 📖 [Podman Systemd Unit Documentation](https://docs.podman.io/en/latest/markdown/podman-systemd.unit.5.html)
- 🔴 [Red Hat Quadlet Guide](https://www.redhat.com/sysadmin/quadlet-podman)
- 💡 [Practical Quadlet Tutorial](https://mo8it.com/blog/quadlet/)

---

## 📋 Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support

### Rootless Setup

This guide assumes a **rootless** Podman setup (recommended for security):

- 📖 [Rootless Podman Tutorial](https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md)
- 🔧 [Arch Linux Podman Guide](https://wiki.archlinux.org/title/Podman#Rootless_Podman)

> [!TIP]
> Your distribution may have already configured rootless Podman for you.

To allow GPU acceleration when using SELinux:

```bash
sudo setsebool -P container_use_dri_devices 1
```

---

## 🛠️ Installation

### 1️⃣ Configure Container Files

Copy the container configuration files to your systemd user directory:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/podman/systemd/stry ~/.config/containers/systemd/
```

> [!TIP]
> **Alternative (Podman 5.2+):**
>
> You can use `podman quadlet install` for individual unit files:
>
> ```bash
> podman quadlet install ~/projects/stry/podman/systemd/stry/*.{container,build,network,volume}
> ```
>
> However, `cp -r` is recommended as it preserves the `config/` subdirectory structure with environment files.

### 2️⃣ Adjust Container Configuration

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

### 3️⃣ Setup Storage Paths

Create the required data directories as defined in `app.env`:

```bash
mkdir -p /home/user/data/stry/{media,import}
```

### SELinux & Volume Flags (if applicable)

If using SELinux, configure the proper permissions. Use:

- `:z` when the volume content is shared between multiple containers.
- `:Z` when the volume should be private to a single container.
- `,U` can be added when user namespace remapping requires ownership fix-ups (rootless environments with keep-id may omit it unless issues arise).

Example:

```ini
Volume=${MEDIA_PATH}:/data/media:rw,z,U
```

### Environment Variables (`app.env`)

| Key             | Purpose                                                   |
| --------------- | --------------------------------------------------------- |
| `UID`, `GID`    | Mapped user/group IDs for rootless container processes    |
| `CONTAINER_ENV` | Application environment (e.g. production)                 |
| `APP_PATH`      | Host path of source checkout (bind if enabling live code) |
| `MEDIA_PATH`    | Host data directory containing media subfolders           |
| `IMPORT_PATH`   | Host data directory containing files to be imported       |

Ensure both paths exist and are owned by the matching UID/GID:

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
| Garage       | 3900–3903 |

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

### 4️⃣ Apply Configuration Changes

Reload systemd to recognize the new containers:

```bash
systemctl --user daemon-reload
```

### 5️⃣ Configure S3 Storage

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
> - Performs [storage-chown-by-maps](https://github.com/containers/podman/issues/13071) for rootless user namespaces
>
> **Do not cancel this process!** If needed, increase the `TimeoutStartSec=*` value in your container files.

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

## 💡 Troubleshooting

> [!TIP]
> **Common Issues:**
>
> - **Container won't start**: Check `journalctl --user -u stry` for errors
> - **Permission issues**: Verify SELinux contexts and directory ownership
> - **Timeout errors**: Increase timeout values in container files
> - **Port conflicts**: Ensure no other services are using the same ports
> - **Rebuild needed**: See the [Upgrading](#-upgrading) section above

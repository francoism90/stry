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

---

## 🛠️ Installation

### 1️⃣ Configure Container Files

Copy the container configuration files:

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/podman/systemd/stry ~/.config/containers/systemd/
```

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
Volume=${DATA_PATH}:/data:rw,z,U
```

### Environment Variables (`app.env`)

| Key          | Purpose                                                |
|--------------|--------------------------------------------------------|
| `UID`, `GID` | Mapped user/group IDs for rootless container processes |
| `CONTAINER_ENV` | Application environment (e.g. production)           |
| `APP_PATH`   | Host path of source checkout (bind if enabling live code) |
| `DATA_PATH`  | Host data directory containing media/import subfolders |

Ensure both paths exist and are owned by the matching UID/GID:

```bash
chown -R 1000:1000 /home/user/projects/stry /home/user/data/stry
```

### Exposed Ports

Primary application & services expose:

| Service      | Port |
|--------------|------|
| App (HTTP)   | 8080 |
| Vite (Dev)   | 5173 |
| Reverb (WS)  | 6001 |
| SSR Renderer | 13714 |
| Mailpit SMTP | 1025 |
| Mailpit UI   | 8025 |
| PostgreSQL   | 5432 |
| Redis        | 6379 |
| Typesense    | 8108 |
| Garage       | 3900–3903 |

Ensure these are free on the host or adjust the `ExposeHostPort` lines in the respective container unit files.

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

---

## 💡 Troubleshooting

> [!TIP]
> **Common Issues:**
>
> - **Container won't start**: Check `journalctl --user -u stry` for errors
> - **Permission issues**: Verify SELinux contexts and directory ownership
> - **Timeout errors**: Increase timeout values in container files
> - **Port conflicts**: Ensure no other services are using the same ports

### Rebuild Containers

If you need to rebuild the image after changes:

```bash
systemctl --user stop stry
systemctl --user restart stry-build
systemctl --user daemon-reload
systemctl --user start stry
```

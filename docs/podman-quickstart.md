---
title: Podman Quadlet Quick Start
order: 3
tags:
    - podman
    - quadlet
    - containers
    - systemd
---

# Podman Quadlet Quick Start

## Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support

### Rootless Setup (Recommended)

This guide assumes **rootless** Podman (recommended for security):

- 📖 [Rootless Podman Tutorial](https://github.com/containers/podman/blob/main/docs/tutorials/rootless_tutorial.md)
- 🔧 [Arch Linux Podman Guide](https://wiki.archlinux.org/title/Podman#Rootless_Podman)

Enable lingering to keep containers running when you log out:

```bash
loginctl enable-linger $USER
```

#### GPU Acceleration (Optional)

If using rootless Podman with GPU and SELinux:

```bash
sudo setsebool -P container_use_dri_devices 1
sudo usermod -aG render,video $USER
sudo reboot
```

---

## Installation Steps

### 1. Copy Container Configuration

```bash
mkdir -p ~/.config/containers/systemd
cp -r ~/projects/stry/containers/podman/systemd/stry ~/.config/containers/systemd/
```

### 2. Create Media & Import Directories

```bash
mkdir -p /home/user/data/stry/media
mkdir -p /home/user/data/stry/import
```

### 3. Configure app.env

Edit `~/.config/containers/systemd/stry/config/app.env` with your deployment settings:

```bash
vi ~/.config/containers/systemd/stry/config/app.env
```

**Required values:**

- `APP_KEY` — generate with `php artisan key:generate --show` from your repo
- `MEDIA_PATH` — `/home/user/data/stry/media` (must match directory created above)
- `IMPORT_PATH` — `/home/user/data/stry/import` (must match directory created above)
- `APP_URL` — your deployment domain (e.g. `https://stry.example.com`)
- Database, Redis, and API credentials

Optional values:

- `UID` / `GID` — only set if you need non-default user IDs (defaults to 1000/1000)

### 4. Configure Other Env Files

Edit credentials for PostgreSQL, Typesense, and RustFS:

```bash
vi ~/.config/containers/systemd/stry/config/postgres.env
vi ~/.config/containers/systemd/stry/config/typesense.env
vi ~/.config/containers/systemd/stry/config/rustfs.env
```

### 5. Reload systemd and Start

```bash
systemctl --user daemon-reload
systemctl --user start stry
```

> [!TIP]
> First start can take several minutes (migrations, optimization, PWA generation). Check progress:
>
> ```bash
> journalctl --user -u stry -f
> ```

---

## Basic Commands

```bash
# Check status
systemctl --user status stry

# View logs
journalctl --user -u stry -f

# Restart services
systemctl --user restart stry

# Stop services
systemctl --user stop stry
```

---

## Next Steps

- **[Podman Configuration](podman-configuration.md)** — Detailed setup and SELinux guidance
- **[Podman Operations](podman-operations.md)** — Container management and upgrades
- **[Podman Reference](podman-reference.md)** — Resource limits and security hardening

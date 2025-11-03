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
cp -r ~/projects/stry/containers/systemd/stry ~/.config/containers/systemd/
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

#### SELinux Configuration (if applicable)

If using SELinux, configure the proper permissions:

```bash
sudo semanage fcontext -a -t container_file_t '/home/user/data/stry/import(/.*)?'
sudo restorecon -R -v /home/user/data/stry/import
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
> - Installs vendor packages
> - Runs [storage-chown-by-maps](https://github.com/containers/podman/issues/13071)
>
> **Do not cancel this process!** If needed, increase the `timeout=*` value in your container files.

To build and start all containers:

```bash
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

If you need to rebuild after changes:

```bash
systemctl --user stop stry
podman pod rm -f stry
systemctl --user daemon-reload
systemctl --user start stry
```

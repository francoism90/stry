---
title: Running on production
order: 1
tags:
  - podman
  - quadlet
  - usage
---

# 🚀 Production Setup

## 📋 Prerequisites

**System Requirements:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support

> [!WARNING]
> Ensure your system meets all prerequisites before proceeding with production deployment.

---

## 🛠️ Installation

### 1️⃣ Clone the Project

Clone the repository to your working directory (e.g., `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

### 2️⃣ Configure Podman

Follow the comprehensive [Podman Quadlet](podman.md) guide for container setup.

> [!TIP]
> Make sure to review the Podman guide thoroughly to ensure proper configuration for production.

### 3️⃣ Setup Proxy (Required)

A reverse proxy is **required** to interact with the container services securely.

Follow the [Proxy Setup](proxy.md) guide for detailed configuration.

---

## 🔒 Security Considerations

> [!IMPORTANT]
> **Production Checklist:**
>
> - ✅ Use strong, unique passwords for all services
> - ✅ Configure firewall rules appropriately
> - ✅ Enable HTTPS with valid SSL certificates
> - ✅ Regularly update containers and dependencies
> - ✅ Set up automated backups
> - ✅ Monitor logs and system resources
> - ✅ Never use development/testing seeders in production

---

## ⚡ Performance Optimization

### Reduce Container Logging Overhead

For production deployments, consider disabling Podman container logging to prevent performance degradation:

Add `LogDriver=none` to your container files in `~/.config/containers/systemd/stry/*.container`:

```ini
[Container]
LogDriver=none
```

> [!TIP]
> **Benefits of `LogDriver=none`:**
>
> - ✅ Eliminates disk I/O overhead from container logs
> - ✅ Prevents log files from consuming disk space
> - ✅ Reduces CPU usage from logging operations
> - ✅ Improves overall container performance
>
> **Trade-offs:**
>
> - ⚠️ Container logs won't be available via `journalctl` or `podman logs`
> - ⚠️ Application logs remain accessible via Laravel's logging system
> - ⚠️ Use Laravel Telescope/Horizon for monitoring instead

If you need selective logging, apply `LogDriver=none` only to high-throughput containers (e.g., `stry-queue.container`, `stry-reverb.container`) while keeping logs enabled for critical services.

---

## 📊 Next Steps

After installation:

1. 📖 Review the [Configuration](configuration.md) guide
2. 🔧 Set up [S3 Storage](s3.md) for media files
3. 🎮 Learn about [Interaction](interaction.md) commands
4. 📈 Monitor your instance with [Laravel Horizon](https://stry.test/horizon)

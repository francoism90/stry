---
title: System Tuning
order: 10
tags:
    - performance
    - resources
    - optimization
---

# System Resource Tuning

## Overview

This guide covers runtime resource management for **stry** containers. For comprehensive resource reference including pre-configured defaults, see **[Podman Reference](podman-reference.md)**.

> [!TIP]
> Container files ship with sensible defaults tuned for ~16GB development machines. Most deployments work with defaults—only adjust if needed.

---

## Quick Reference

Default resource allocation (for ~16GB machine):

| Container      | Memory    | Notes                     |
| -------------- | --------- | ------------------------- |
| `stry`         | 2 GB      | Scale with Octane workers |
| `stry-queue`   | 3 GB      | FFmpeg needs resources    |
| `stry-pgsql`   | 2 GB      | 4-8 GB in production      |
| `stry-redis`   | 512 MB    | 1-2 GB in production      |
| Other services | 256MB-1GB | See reference for details |

See **[Podman Reference: Resource Limits](podman-reference.md#resource-limits)** for complete table.

---

## ⚡ Real-time Resource Management

### Set CPU Limits

Limit CPU cores for a running container:

```bash
# Limit to 5 CPU cores
podman update systemd-stry-queue --cpus=5

# Limit to 2.5 CPU cores (fractional)
podman update systemd-stry --cpus=2.5
```

### Set Memory Limits

```bash
# Limit to 2GB RAM
podman update systemd-stry-queue --memory=2g

# Limit to 512MB RAM
podman update systemd-stry-reverb --memory=512m
```

### Set Both CPU and Memory

```bash
podman update systemd-stry-queue \
    --cpus=4 \
    --memory=4g
```

---

## 📝 Permanent Configuration

### Configure in Container Files

Add resource limits directly to your `.container` files:

```ini
# Example: ~/.config/containers/systemd/stry/stry-queue.container
[Container]
Image=<image>
# ... other settings ...

# Resource limits
PodmanArgs=--cpus=5
PodmanArgs=--memory=4g
```

> [!IMPORTANT]
> After modifying container files, reload systemd:

```bash
systemctl --user daemon-reload
systemctl --user restart stry-queue
```

---

## 📊 Monitoring Resources

### View Container Resource Usage

```bash
# Real-time stats for all containers
podman stats

# Stats for specific container
podman stats systemd-stry-queue

# One-time snapshot (no streaming)
podman stats --no-stream
```

### Check Current Limits

```bash
podman inspect systemd-stry-queue | grep -A5 "Resources"
```

---

## 🎯 Recommended Resource Limits

### Production Guidelines

| Container         | CPUs | Memory      | Notes                                |
| ----------------- | ---- | ----------- | ------------------------------------ |
| `stry` (main app) | 4-8  | 2-4GB       | Main application server              |
| `stry-queue`      | 4-6  | 2-4GB       | Background job processing            |
| `stry-reverb`     | 1-2  | 512MB-1GB   | WebSocket server                     |
| `stry-schedule`   | 1    | 256MB-512MB | Task scheduler (low usage)           |
| `stry-postgres`   | 2-4  | 2-8GB       | Database (adjust based on data size) |
| `stry-redis`      | 1-2  | 512MB-2GB   | Cache & sessions                     |
| `stry-typesense`  | 2-4  | 1-4GB       | Search engine                        |
| `stry-garage`     | 2-4  | 1-2GB       | S3 storage                           |

> [!NOTE]
> These are starting points. Adjust based on your actual usage patterns and available system resources.

---

## 🔍 Advanced Resource Controls

### CPU Quotas and Periods

```bash
# Set CPU quota (100000 = 1 core)
podman update systemd-stry --cpu-quota=400000  # 4 cores

# Set CPU period (default: 100000 microseconds)
podman update systemd-stry --cpu-period=100000
```

### Memory Swap

```bash
# Disable swap for container
podman update systemd-stry-queue --memory-swap=-1

# Set swap to 2x memory (4GB RAM + 4GB swap)
podman update systemd-stry-queue --memory=4g --memory-swap=8g
```

### I/O Limits

```bash
# Limit read/write operations per second
podman update systemd-stry-queue \
    --blkio-weight=500 \
    --device-read-bps=/dev/sda:10mb \
    --device-write-bps=/dev/sda:50mb
```

---

## 💡 Best Practices

> [!TIP]
> **Performance Tips:**
>
> - 🔍 **Monitor first**: Use `podman stats` to understand actual resource usage
> - 📈 **Set realistic limits**: Don't over-provision or under-provision
> - 🔄 **Test thoroughly**: Verify application works under limits
> - 📊 **Leave headroom**: Allow 10-20% buffer for spikes
> - 🎯 **Prioritize**: Give more resources to critical containers
> - ⚠️ **Watch OOM**: Monitor for Out-of-Memory kills in logs

---

## 🚨 Troubleshooting

### Container Killed (OOM)

If containers are being killed due to memory:

```bash
# Check for OOM kills in logs
journalctl --user -u stry-queue | grep -i "killed"

# Increase memory limit
podman update systemd-stry-queue --memory=8g
systemctl --user restart stry-queue
```

### High CPU Usage

```bash
# Monitor CPU usage
podman stats systemd-stry-queue

# Reduce CPU limit if container is using too much
podman update systemd-stry-queue --cpus=2
```

### View Resource Constraints

```bash
# Show all resource limits for a container
podman inspect systemd-stry-queue \
    --format '{{.HostConfig.NanoCpus}} {{.HostConfig.Memory}}'
```

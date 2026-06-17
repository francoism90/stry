---
title: Development
order: 2
tags:
    - vscode
    - podman
    - devcontainer
    - boost
    - ai
---

# Development Setup

## Prerequisites

**Required Tools:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support
- 💻 [VSCode](https://code.visualstudio.com/) with [Podman extension](https://github.com/jorchube/devcontainer-definitions)
- 🤖 [GitHub Copilot](https://github.com/features/copilot) (optional, recommended)

---

## Installation

### Setup Project

Clone the project to your working directory (e.g., `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
cd stry
```

### Setup Containers

First, follow the [Podman Quick Start](podman-quickstart.md) guide to set up basic container configuration. Then apply these development-specific adjustments:

**Step 1:** Set development environment in `app.env`:

```bash
vi ~/.config/containers/systemd/stry/config/app.env
```

Update to:

```env
APP_ENV=local
APP_DEBUG=true
```

**Step 2:** Mount application code for live editing. Edit these container files:

- `~/.config/containers/systemd/stry/stry.container`
- `~/.config/containers/systemd/stry/stry-queue.container`
- `~/.config/containers/systemd/stry/stry-reverb.container`
- `~/.config/containers/systemd/stry/stry-schedule.container`

Add this line to the `[Container]` section of each file (replace `/path/to/stry` with your actual path):

```ini
Volume=/path/to/stry:/app:rw,z,U
```

Example for `stry.container`:

```ini
[Container]
Image=stry.build
Volume=/path/to/stry:/app:rw,z,U
# ... other configuration ...
```

> [!NOTE]
> The `z` flag allows the container to access the mounted volume, and `U` ensures proper user mapping in rootless Podman.

**Step 3 (Optional):** Skip the SSR renderer in development by removing this line from `stry.container`:

```diff
-Wants=stry-ssr.container
```

**Step 4:** Reload and restart:

```bash
systemctl --user daemon-reload
systemctl --user restart stry
```

### Initialize Development Environment

Enter the container shell and run setup commands:

```bash
podman exec -it systemd-stry /bin/bash

# Inside the container:
composer install
php artisan storage:link
php artisan key:generate
php artisan migrate --seed
php artisan scout:sync
pnpm install
```

### Configure Proxy

Follow the [Proxy Setup](proxy.md) guide to enable local HTTPS access.

### Start Development Watchers

Run the Vite development server for live asset rebuilding:

```bash
# From your host machine (repo root):
pnpm dev
```

Or from inside the container:

```bash
podman exec -it systemd-stry pnpm dev
```

> [!TIP]
> Vite watches for changes and automatically rebuilds assets. Your browser will refresh automatically with the new changes via the Vite HMR websocket.

---

## 🧠 IDE Setup

### VS Code Devcontainer

Open the project in VS Code and install the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers):

```bash
code ~/projects/stry
```

The `.devcontainer/` configuration will automatically connect to the running `systemd-stry` container, providing:

- Full PHP IntelliSense and debugging
- Integrated terminal inside the container
- Extension support (ESLint, Prettier, Pest, Inertia, etc.)

### Laravel IDE Helper

Generate IDE helper files for better autocomplete and type hints:

```bash
podman exec systemd-stry php artisan ide-helper:generate
podman exec systemd-stry php artisan ide-helper:meta
podman exec systemd-stry php artisan ide-helper:models --nowrite
```

---

## 🤖 AI-Powered Development

### Laravel Boost

Enable [Laravel Boost](https://boost.laravel.com/) MCP server for AI-powered Laravel development:

1. Open VS Code Command Palette: `Cmd+Shift+P` (Mac) or `Ctrl+Shift+P` (Windows/Linux)
2. Search and select: "MCP: List Servers"
3. Find and start: `laravel-boost`

✅ You now have AI assistance for Laravel development with Copilot!

### GitHub Copilot

Install the [GitHub Copilot extension](https://marketplace.visualstudio.com/items?itemName=GitHub.copilot) for inline AI code suggestions.

---

## 🧪 Testing

### Run Tests

```bash
# Run all tests
podman exec systemd-stry php artisan test

# Run with coverage
podman exec systemd-stry php artisan test --coverage

# Run specific test file
podman exec systemd-stry php artisan test tests/Feature/SomeTest.php

# Run with filter
podman exec systemd-stry php artisan test --filter=testMethodName
```

### Code Quality

```bash
# Run Pint (Laravel code formatter)
podman exec systemd-stry vendor/bin/pint

# Run Larastan (static analysis)
podman exec systemd-stry vendor/bin/larastan
```

---

## 🐛 Debugging

### Laravel Tinker

Interactive REPL for testing code:

```bash
podman exec -it systemd-stry php artisan tinker
```

### View Logs

Real-time application logs:

```bash
# Laravel logs
podman exec systemd-stry tail -f storage/logs/*.log

# System logs
journalctl --user -u stry -f
```

### Database Inspection

```bash
# Access PostgreSQL shell
podman exec -it systemd-stry-pgsql psql -U user -d stry

# Run migrations
podman exec systemd-stry php artisan migrate

# Seed the database
podman exec systemd-stry php artisan db:seed
```

---

## 📦 Dependency Management

### Composer

```bash
# Install dependencies
podman exec systemd-stry composer install

# Update dependencies
podman exec systemd-stry composer update

# Add new package
podman exec systemd-stry composer require vendor/package
```

### npm / pnpm

```bash
# Install frontend dependencies
podman exec systemd-stry pnpm install

# Add package
podman exec systemd-stry pnpm add package-name

# Remove package
podman exec systemd-stry pnpm remove package-name
```

---

## 💾 Database Management

### Fresh Database

Reset everything and start fresh:

```bash
podman exec systemd-stry php artisan migrate:fresh --seed
```

### Backup Database

```bash
podman exec systemd-stry-pgsql pg_dump -U user -d stry > backup.sql
```

### Restore Database

```bash
podman exec -i systemd-stry-pgsql psql -U user -d stry < backup.sql
```

---

## 🔄 Rebuilding After Changes

If you modify dependencies or PHP configuration, rebuild the image:

```bash
systemctl --user restart stry-build
systemctl --user restart stry
```

If you only changed code (with mounted volume), restart without rebuilding:

```bash
systemctl --user restart stry
```

---

## 🆘 Troubleshooting

### Container Won't Start

Check logs:

```bash
journalctl --user -u stry -f
```

Common issues:

- **Missing app.env** — Ensure `~/.config/containers/systemd/stry/config/app.env` exists
- **Missing APP_KEY** — Generate with `php artisan key:generate`
- **Port conflicts** — Check if ports 8000, 5173, 6001 are free

### Permission Issues

If files created in container aren't writable on host:

```bash
# Fix ownership (replace with your uid/gid)
chown -R 1000:1000 ~/projects/stry/storage
```

### Assets Not Compiling

Clear Vite cache and rebuild:

```bash
rm -rf ~/projects/stry/bootstrap/ssr
podman exec systemd-stry npm run build
```

---

## 🚀 Next Steps

- Explore [CLI Interaction](interaction.md) for helpful commands
- Review [Podman Operations](podman-operations.md) for container management
- Check [Application Configuration](configuration.md) for app-specific settings

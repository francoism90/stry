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

# 💻 Development Setup

## 📋 Prerequisites

**Required Tools:**

- 🐧 Linux (Debian, Fedora, CentOS, Arch, Ubuntu, etc.)
- 🐳 [Podman 5.3+](https://podman.io/) with Quadlet (systemd) support
- 💻 [VSCode](https://code.visualstudio.com/) with [Podman extension](https://github.com/jorchube/devcontainer-definitions)
- 🤖 [GitHub Copilot](https://github.com/features/copilot) (optional, recommended)

---

## 🚀 Installation

### 1️⃣ Setup Project

Clone the project to your working directory (e.g., `~/projects`):

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
```

### 2️⃣ Configure Podman

> [!NOTE]
> See the [Podman Quadlet](podman.md) guide for complete details.

Apply the following adjustments for development:

**Step 1:** Change environment to development in your config:

```bash
# Edit: ~/.config/containers/systemd/stry/config/app.env
CONTAINER_ENV=development
```

**Step 2:** Add the app volume to these containers:

- `stry.container`
- `stry-queue.container`
- `stry-reverb.container`
- `stry-schedule.container`

Add this line to each container file:

```diff
+Volume=${APP_PATH}:/app:rw,z,U
Volume=${DATA_PATH}:/data:rw,z,U
```

> [!IMPORTANT]
> The volume `U` flag should **only** be appended in `stry.container`.

**Step 3:** Remove SSR dependency:

```diff
-Wants=stry-ssr.container
```

### 3️⃣ Setup Development Container

Open the cloned project in VSCode as a devcontainer (recommended) or enter the container:

```bash
podman exec -ti systemd-stry /bin/bash
```

Run the initial setup commands:

```bash
composer install
php artisan storage:link
php artisan key:generate
php artisan migrate --seed
php artisan google-fonts:fetch
pnpm install
```

### 4️⃣ Configure Proxy

Follow the [Proxy Setup](proxy.md) guide to enable local HTTPS access.

### 5️⃣ Start Development Watchers

Run the Vite development server:

```bash
stry pnpm dev
```

> [!TIP]
> The watcher will automatically rebuild assets when you make changes to frontend files.

---

## 🔧 IDE Integration

For enhanced [IDE support](https://github.com/barryvdh/laravel-ide-helper) with Laravel autocomplete and type hints:

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models --nowrite
```

---

## 🤖 Laravel Boost

To enable [Laravel Boost](https://boost.laravel.com/installed) AI assistance:

1. **Open Command Palette**: `Cmd+Shift+P` (Mac) or `Ctrl+Shift+P` (Windows/Linux)
2. **Select**: "MCP: List Servers"
3. **Choose**: `laravel-boost`
4. **Action**: Select "Start server"

✅ You're ready to use AI-powered Laravel development!

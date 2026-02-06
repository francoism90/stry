---
title: Interaction
order: 5
tags:
    - fish
    - shell
    - bash
    - commands
---

# 🎮 CLI Interaction

## 📝 Introduction

**stry** provides a shell utility similar to [Laravel Sail](https://github.com/laravel/sail/blob/1.x/bin/sail), adapted for Podman Quadlet.

> [!NOTE]
> This utility works with both **production** and **development** environments.

---

## ⚙️ Setup Shell Alias

Create a shell alias for easy access. For [fish-shell](https://fishshell.com/docs/current/cmds/alias.html):

```fish
alias --save stry '~/projects/stry/bin/quadlet'
```

For **bash/zsh**, add to your `~/.bashrc` or `~/.zshrc`:

```bash
alias stry='~/projects/stry/bin/quadlet'
```

> [!TIP]
> After setting the alias, restart your shell or run `source ~/.bashrc` (or equivalent).

---

## 🚀 Usage Examples

Interact with your Podman containers using intuitive commands:

### Basic Commands

```bash
stry help                           # Show all available commands
stry shell                          # Enter the main container shell
stry tinker                         # Open Laravel Tinker REPL
```

### Artisan Commands

```bash
stry artisan optimize               # Optimize Laravel application
stry a migrate                      # Run database migrations (shorthand)
stry a horizon:forget --all         # Clear Horizon failed jobs
stry a videos:import                # Import videos
```

### Service Interactions

```bash
stry redis flushall                 # Flush Redis cache
stry garage bucket list             # List S3 buckets
```

---

## 🐳 Direct Podman Access

You can also interact with containers directly using Podman:

```bash
# Execute commands in containers
podman exec -it systemd-stry php artisan help
podman exec -it systemd-stry-queue /bin/bash
podman exec -ti systemd-stry-redis /bin/bash
```

---

## 📋 Available Commands

Run `stry help` for a complete list. Here are the most commonly used:

### 👥 User Management

| Command               | Description               |
| --------------------- | ------------------------- |
| `stry a users:create` | Create a new user account |

### 🎬 Video Management

| Command                | Description                                |
| ---------------------- | ------------------------------------------ |
| `stry a videos:import` | Import videos for a user                   |
| `stry a videos:clear`  | Remove soft-deleted videos from filesystem |

### 🏷️ Tag Management

| Command              | Description                      |
| -------------------- | -------------------------------- |
| `stry a tags:create` | Create a new tag                 |
| `stry a tags:sort`   | Sort tags alphabetically by type |

### 📹 Playlist & Media

| Command                                                      | Description                                     |
| ------------------------------------------------------------ | ----------------------------------------------- |
| `stry a playlists:clear`                                     | Remove generated DASH playlists from filesystem |
| `stry a groups:clear`                                        | Detach all videos from groups of a given type   |
| `stry a media-library:regenerate --only-missing --queue-all` | Regenerate missing media conversions            |

### 🔍 Search & Indexing

| Command                           | Description                                                               |
| --------------------------------- | ------------------------------------------------------------------------- |
| `stry a scout:sync`               | Sync searchable model indexes (re-import all models)                      |
| `stry a scout:sync --delete`      | Delete and re-sync all indexes (clears existing data before re-importing) |
| `stry a scout:delete-index Model` | Delete a specific index (useful for fixing corrupted indexes)             |
| `stry a scout:import Model`       | Import a specific model into its search index                             |

---

## 💡 Tips & Tricks

> [!TIP]
> **Pro Tips:**
>
> - Use `stry a` as shorthand for `stry artisan`
> - Run `stry shell` to enter the container and execute multiple commands
> - Use `stry tinker` for quick Laravel/database testing
> - Check logs with `stry logs` or specific containers with `podman logs systemd-stry`

---
title: Development
section: Getting Started
order: 4
tags:
    - vscode
    - zed
    - podman
    - devcontainer
    - boost
    - ai
---

# Development Setup

## What you need

- Linux with systemd and [Podman 6+](https://podman.io/)
- Optional: [`lpod`](https://github.com/foxws/lpod), for the `lpod stry …` commands below (see [Podman Quadlet](podman.md#prerequisites) for how to install it)
- Optional: [VS Code](https://code.visualstudio.com/) or [Zed](https://zed.dev/) with the [Podman SDK extension](https://github.com/francoism90/org.freedesktop.Sdk.Extension.podman)

## Setup

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
cd stry
composer install
cp .env.example .env
php artisan key:generate
```

The units in `containers/quadlets/development/` run every service against your checkout, so your changes show up right away. They expect the checkout in `~/projects/stry`; if it's somewhere else, change the path in `stry.build` and `stry-source.volume` first. Then build the image and start the app:

```bash
podman quadlet install --application=stry containers/quadlets/development
systemctl --user start stry
```

The first start builds the development image, which takes a while. To test a production build locally, install `containers/quadlets/production/` instead (see [Production Setup](production.md)).

The development units read `.env` from your checkout, so there are no secrets to create. Set `APP_ENV=local`, `APP_DEBUG=true` and `PWA_ENABLED=false` in it, plus any other local settings you need.

When it's running, the app is available at `http://localhost:8000`. You don't need a reverse proxy locally. See [Reverse Proxy](proxy.md) if you want to test the subdomain routing used in production.

Once the containers are up, install the dependencies and seed the database:

```bash
lpod stry shell
composer install
php artisan storage:link
php artisan migrate --seed
php artisan scout:sync --import
pnpm install
```

`stry-vite` runs the Vite dev server in its own container, next to `stry`. You don't need to run `pnpm dev` yourself.

### Admin account

For testing, you can seed a super-admin user:

```bash
lpod stry a db:seed --class=AdminSeeder
```

:::warning
Only use this seeder for testing. Never run it in production. See the [security checklist](production.md#security-checklist).
:::

You can also create an admin interactively, without the seeder: `lpod stry artisan users:create --super-admin` (see [CLI Interaction](interaction.md#users)).

## VS Code Dev Containers

`containers/devcontainer/devcontainer.json` is a config for the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers), so you can develop **stry** inside a container. This is separate from the units above, which run the app as a service. It connects to the `systemd-stry` network and gives you PHP IntelliSense, debugging, a terminal and the Claude Code and Codex CLIs inside the container. Symlink it, so it stays up to date when you pull:

```bash
mkdir -p .devcontainer
ln -sf ../containers/devcontainer/devcontainer.json .devcontainer/devcontainer.json
code ~/projects/stry
```

To change it, for example to mount your Git or Claude Code login, copy it to `.devcontainer/devcontainer.json` instead and edit the copy. `.devcontainer/` isn't tracked by Git.

:::note
`~/.claude.json` must exist as a file on your machine before the first launch. Otherwise Podman creates an empty directory with that name instead.
:::

After changing it, run **Dev Containers: Rebuild Container**.

### Laravel IDE Helper

```bash
lpod stry artisan ide-helper:generate
lpod stry artisan ide-helper:meta
lpod stry artisan ide-helper:models --nowrite
```

## AI-assisted development

[Laravel Boost](https://boost.laravel.com/) is set up as an MCP server. In VS Code, open the Command Palette (`Ctrl+Shift+P` or `Cmd+Shift+P`), choose **MCP: List Servers** and start `laravel-boost`.

The `-ai` devcontainer config (see [above](#vs-code-dev-containers)) also installs the `claude` and `codex` CLIs. Your `~/.claude` and `~/.codex` folders are mounted into the container, so you stay logged in after a rebuild. Use either CLI together with Boost, which gives it Laravel-specific context such as routes, the database schema, config and Tinker.

## Testing and code quality

```bash
lpod stry artisan test
lpod stry artisan test --filter=testMethodName
lpod stry bin pint
lpod stry bin larastan
```

## Admin services

Available when you're logged in as a **super-admin**:

| Service       | URL                               | Description                          |
| ------------- | --------------------------------- | ------------------------------------ |
| **Horizon**   | `http://localhost:8000/horizon`   | Monitor and manage queues            |
| **Telescope** | `http://localhost:8000/telescope` | Debugging tool (only in development) |

## Troubleshooting

- **A container won't start**: run `journalctl --user -u stry -f`. Look for a missing or invalid `stry-env` secret, or another process using port 8000, 5173 or 6001.
- **Permission errors**: run `sudo chown -R "$USER:" ~/projects/stry/storage`. The containers run as your own user, so this is only needed after running a command as root, such as `podman exec` without `--user docker`.
- **Permission denied on `/media` or `/import`**: on SELinux hosts, label your media folders (`/srv/library` by default) once for containers. See [Storage, ownership & SELinux](podman.md#storage-ownership--selinux).
- **The database or caches are empty after regenerating the Podman files**: the `development` volumes used to be named `systemd-stry-pgsql`, `systemd-stry-cache` and so on. They're now named like the production ones, `stry-pgsql` and `stry-cache`, so the services start with new volumes. Your old data is still in the `systemd-stry-*` volumes. For the database, run `lpod stry artisan migrate --seed` to start fresh, or copy the old volume over while the services are stopped: `podman volume export systemd-stry-pgsql | podman volume import stry-pgsql -`. Once you no longer need them, list the old volumes with `podman volume ls --filter name=systemd-stry` and remove them with `podman volume rm`.
- **Assets don't build**: run `rm -rf bootstrap/ssr && lpod stry npm run build`.
- **Tests fail with `could not translate host name "systemd-stry-pgsql"`**: you ran `php artisan test` on your machine instead of inside the container network. Start the containers with `lpod stry up`, then run `lpod stry artisan test`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app settings

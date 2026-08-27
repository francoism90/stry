---
title: Development
sidebar_position: 3
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

- Linux with systemd, [Podman 5.3+](https://podman.io/)
- [`lpod`](https://github.com/foxws/lpod) — see [Podman Quadlet](podman.md#prerequisites) for the install command
- [VSCode](https://code.visualstudio.com/) or [Zed](https://zed.dev/) with the [Podman SDK extension](https://github.com/francoism90/org.freedesktop.Sdk.Extension.podman) (optional)

## Setup

```bash
cd ~/projects
git clone git@github.com:francoism90/stry.git
cd stry
composer install
cp .env.example .env
php artisan key:generate
```

Pick one preset for the app image (see [Podman Quadlet](podman.md) for the full service list):

- **`development`** — mounts your working copy into the container live, so edits show up instantly. Use this day to day.
- **`frankenphp-octane`** — the same image production uses, code baked in. Use this to test a production-style build locally.

```bash
php artisan podman:setup --preset=development
# or: --preset=frankenphp-octane

lpod install development/app.quadlets --replace
lpod install development/pgsql.quadlets --replace
# ...and so on for every service (see podman.md)
```

:::tip
Set `PODMAN_DEFAULT_PRESETS` in `.env` (comma-separated, e.g. `PODMAN_DEFAULT_PRESETS=development,s3,devcontainer`) to skip passing `--preset` on every `podman:setup` run.
:::

Once it's up, the app is available directly at `http://localhost:8000` — no reverse proxy needed locally (see [Reverse Proxy](proxy.md) if you want to test the production-style subdomain routing).

Set `APP_ENV=local`/`APP_DEBUG=true`/`PWA_ENABLED=false` (and any other local overrides) before storing them with `lpod stry secrets`.

Once the containers are up, install dependencies and seed data:

```bash
lpod stry shell
composer install
php artisan storage:link
php artisan migrate --seed
php artisan scout:sync --import
pnpm install
```

The `development` preset's `vite.quadlets` runs the Vite dev server in its own container, so it comes up alongside `stry` — no need to run `pnpm dev` from the host:

```bash
lpod install development/vite.quadlets --replace
```

### Admin account

For testing only, seed a super-admin user:

```bash
lpod stry a db:seed --class=AdminSeeder
```

:::warning
Only seed admins for testing! Never use the seeder in production — see the [Security checklist](production.md#security-checklist).
:::

Alternatively, create one interactively without a seeder: `lpod stry artisan users:create --super-admin` (see [CLI Interaction](interaction.md#users)).

## VS Code Dev Containers

The `devcontainer` preset uses the prebuilt `ghcr.io/foxws/laravel-podman-devcontainer` image for the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers). With `stry` already running:

```bash
code ~/projects/stry
```

`.devcontainer/devcontainer.json` connects to the `systemd-stry` network and gives you PHP IntelliSense, debugging, and an integrated terminal.

### Laravel IDE Helper

```bash
lpod stry artisan ide-helper:generate
lpod stry artisan ide-helper:meta
lpod stry artisan ide-helper:models --nowrite
```

## AI-assisted development

[Laravel Boost](https://boost.laravel.com/) is wired up as an MCP server — in VS Code, open the Command Palette (`Ctrl+Shift+P`/`Cmd+Shift+P`) → "MCP: List Servers" → start `laravel-boost`.

## Testing & code quality

```bash
lpod stry artisan test
lpod stry artisan test --filter=testMethodName
lpod stry bin pint
lpod stry bin larastan
```

## Admin services

Accessible when logged in as **super-admin**:

| Service       | URL                               | Description                     |
| ------------- | --------------------------------- | ------------------------------- |
| **Horizon**   | `http://localhost:8000/horizon`   | Queue monitoring and management |
| **Telescope** | `http://localhost:8000/telescope` | Debugging assistant (dev only)  |

## Troubleshooting

- **Container won't start** — `journalctl --user -u stry -f`; check for a missing/invalid `stry-env` secret or a port conflict (8000, 5173, 6001).
- **Permission issues** — `chown -R 1000:1000 ~/projects/stry/storage` (match your `PODMAN_QUADLET_UID`/`GID` if you changed them).
- **Assets not compiling** — `rm -rf bootstrap/ssr && lpod stry npm run build`.
- **Tests fail with `could not translate host name "systemd-stry-pgsql"`** — you're running `php artisan test` directly on the host instead of inside the container network. Run `lpod stry up` first, then use `lpod stry artisan test`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app-specific settings

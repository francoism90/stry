---
title: Development
order: 2
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

Pick one preset for the app image, then generate and install it together with `proxy` (see [Podman Quadlet](podman.md) for the full service list):

- **`development`** — mounts your working copy into the container live, so edits show up instantly. Use this day to day.
- **`frankenphp-octane`** — the same image production uses, code baked in. Use this to test a production-style build locally.

```bash
php artisan podman:setup --preset=development --preset=proxy
# or: --preset=frankenphp-octane --preset=proxy

lpod install development/app.quadlets --replace
lpod install development/pgsql.quadlets --replace
# ...and so on for every service (see podman.md)
```

Set `APP_ENV=local`/`APP_DEBUG=true` (and any other local overrides) before storing them with `lpod stry secrets`.

Once the containers are up, install dependencies and seed data:

```bash
lpod stry shell
composer install
php artisan storage:link
php artisan migrate --seed
php artisan scout:sync
pnpm install
```

Run the Vite dev server from the host:

```bash
pnpm dev
```

## VS Code Dev Containers

The `devcontainer` preset builds a dedicated image for the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers). With `stry` already running:

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

## Troubleshooting

- **Container won't start** — `journalctl --user -u stry -f`; check for a missing/invalid `stry-env` secret or a port conflict (8000, 5173, 6001).
- **Permission issues** — `chown -R 1000:1000 ~/projects/stry/storage` (match your `PODMAN_QUADLET_UID`/`GID` if you changed them).
- **Assets not compiling** — `rm -rf bootstrap/ssr && lpod stry npm run build`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app-specific settings

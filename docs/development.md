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

## Prerequisites

- Linux with systemd, [Podman 5.3+](https://podman.io/)
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

Choose one preset for the app image, then generate and install it together with `proxy` (see [Podman Quadlet](podman.md) for the full service list):

- **`development`** — live-mounts your working copy into the container, for local editing. Use this day-to-day.
- **`frankenphp-octane`** — production-style image with the app code baked in. Use this to test the production build locally.

```bash
php artisan podman:setup --preset=development --preset=proxy
# or: --preset=frankenphp-octane --preset=proxy

vendor/bin/lpod install development/app.quadlets --replace
vendor/bin/lpod install development/pgsql.quadlets --replace
# ...and so on for every service (see podman.md)
```

Set `APP_ENV=local`/`APP_DEBUG=true` (and any other local overrides) before storing them with `lpod secrets stry`.

Once containers are up, install dependencies and seed data:

```bash
vendor/bin/lpod stry shell
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

`.devcontainer/devcontainer.json` connects to the `systemd-stry` network and provides PHP IntelliSense, debugging, and an integrated terminal.

### Laravel IDE Helper

```bash
vendor/bin/lpod stry artisan ide-helper:generate
vendor/bin/lpod stry artisan ide-helper:meta
vendor/bin/lpod stry artisan ide-helper:models --nowrite
```

## AI-assisted development

[Laravel Boost](https://boost.laravel.com/) is wired up as an MCP server — in VS Code, open the Command Palette (`Ctrl+Shift+P`/`Cmd+Shift+P`) → "MCP: List Servers" → start `laravel-boost`.

## Testing & code quality

```bash
vendor/bin/lpod stry artisan test
vendor/bin/lpod stry artisan test --filter=testMethodName
vendor/bin/lpod stry bin pint
vendor/bin/lpod stry bin larastan
```

## Troubleshooting

- **Container won't start** — `journalctl --user -u stry -f`; check for a missing/invalid `stry-env` secret or a port conflict (8000, 5173, 6001).
- **Permission issues** — `chown -R 1000:1000 ~/projects/stry/storage` (match your `PODMAN_QUADLET_UID`/`GID` if overridden).
- **Assets not compiling** — `rm -rf bootstrap/ssr && vendor/bin/lpod stry npm run build`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app-specific settings

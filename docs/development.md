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

- Linux with systemd and [Podman 5.3+](https://podman.io/)
- [`lpod`](https://github.com/foxws/lpod) (see [Podman Quadlet](podman.md#prerequisites) for how to install it)
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

Choose a preset for the app image (see [Podman Quadlet](podman.md) for the full list of services):

- **`development`** mounts your working copy into the container, so your changes show up right away. Use this for everyday work.
- **`frankenphp-octane`** uses the same image as production, with the code built in. Use this to test a production build locally.

```bash
php artisan podman:setup --preset=development
# or: --preset=frankenphp-octane

lpod install development/app.quadlets --replace
lpod install development/pgsql.quadlets --replace
# ...and so on for every service (see podman.md)
```

:::tip
Set `PODMAN_DEFAULT_PRESETS` in `.env` to a comma-separated list, for example `PODMAN_DEFAULT_PRESETS=development,devcontainer,s3`, so you don't have to pass `--preset` to every `podman:setup` run.
:::

Before you store `.env` with `lpod stry secrets`, set `APP_ENV=local`, `APP_DEBUG=true` and `PWA_ENABLED=false`, plus any other local settings you need.

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

The `development` preset runs the Vite dev server in its own container (`vite.quadlets`), next to `stry`. You don't need to run `pnpm dev` yourself:

```bash
lpod install development/vite.quadlets --replace
```

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

The `devcontainer` preset builds an image for the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers), so you can develop **stry** inside a container. This is separate from the `development` and `frankenphp-octane` presets above, which run the app as a service. With `stry` running, open the project:

```bash
code ~/projects/stry
```

`.devcontainer/devcontainer.json` connects to the `systemd-stry` network and gives you PHP IntelliSense, debugging and a terminal inside the container. Generate the configs, then symlink the one you want. Use a symlink rather than a copy, so it stays up to date when you run `podman:generate` again:

```bash
php artisan podman:generate devcontainer
mkdir -p .devcontainer
ln -sf ../podman/devcontainer/runtimes/devcontainer-ai.json .devcontainer/devcontainer.json
```

**stry** uses the `-ai` config by default, which adds the Claude Code and Codex CLIs to the base image. There are four configs in total: prebuilt or built locally, each with or without the AI CLIs. See [Devcontainer](https://github.com/foxws/laravel-podman/blob/main/docs/devcontainer.md) in the package docs for the others.

:::note
`~/.claude.json` must exist as a file on your machine before the first launch. Otherwise Podman creates an empty directory with that name instead.
:::

After changing the preset, generate the configs again and run **Dev Containers: Rebuild Container**.

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
- **Permission errors**: run `chown -R 1000:1000 ~/projects/stry/storage`. Use your own `PODMAN_QUADLET_UID` and `GID` if you changed them.
- **Assets don't build**: run `rm -rf bootstrap/ssr && lpod stry npm run build`.
- **Tests fail with `could not translate host name "systemd-stry-pgsql"`**: you ran `php artisan test` on your machine instead of inside the container network. Start the containers with `lpod stry up`, then run `lpod stry artisan test`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app settings

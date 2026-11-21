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
- Optional: [`lpod`](https://github.com/foxws/lpod), for the `lpod stry-dev …` commands below (see [Podman Quadlet](podman.md#prerequisites) for how to install it)
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

The units in `containers/quadlets/development/` run every service against your checkout, so your changes show up right away. They expect the checkout in `~/projects/stry`; if it's somewhere else, see [Another checkout folder](#another-checkout-folder) first. Then build the image and start the app:

```bash
podman quadlet install --application=stry-dev containers/quadlets/development
systemctl --user start stry-dev
```

The units, containers and volumes are named `stry-dev`, `systemd-stry-dev-pgsql`, `stry-dev-pgsql` and so on, so they can run next to an installed stry. The first start builds the development image, which takes a while. To test a production build locally, install `containers/quadlets/production/` instead (see [Production Setup](production.md)).

The development units read `.env` from your checkout, so there are no secrets to create. Each container also answers to its production name on the `systemd-stry-dev` network, such as `systemd-stry-pgsql`, so the hosts in `.env.example` work as they are. Set `APP_ENV=local`, `APP_DEBUG=true` and `PWA_ENABLED=false` in it, plus any other local settings you need.

The units don't publish any port on the host, so they don't clash with another user's apps on the same machine. To open the app in your browser, publish its port with a drop-in, for example on port 28000 in `~/.config/containers/systemd/stry-dev.container.d/port.conf`:

```ini
[Container]
PublishPort=28000:8000
```

Run `systemctl --user daemon-reload` and restart `stry-dev`; the app is then at `http://localhost:28000`.

The other services only listen on the `systemd-stry-dev` network too. Your browser reaches Vite, Reverb, RustFS and the Mailpit web UI through the app, by the hostnames in `VITE_HMR_HOST`, `VITE_REVERB_HOST`, `AWS_URL`, `RUSTFS_CONSOLE_HOST` and `MAILPIT_UI_HOST` (see [Reverse Proxy](proxy.md)), so point those names at this machine, for example with a wildcard DNS entry. For the database, cache and search, run their clients inside the containers, such as `lpod stry-dev-pgsql run psql -U user -d stry`.

Once the containers are up, install the dependencies and seed the database:

```bash
lpod stry-dev shell
composer install
php artisan storage:link
php artisan migrate --seed
php artisan scout:sync --import
pnpm install
```

`stry-dev-vite` runs the Vite dev server in its own container, next to `stry-dev`. You don't need to run `pnpm dev` yourself.

### Another checkout folder

If your checkout isn't in `~/projects/stry`, give `stry-dev.build` and `stry-dev-source.volume` a drop-in with its path, instead of editing the files: an update, with Pods or `podman quadlet install --replace`, replaces those files, but keeps your drop-ins. Use absolute paths, as `podman build` looks for a relative `File=` in the service's own directory. For a checkout in `~/code/stry`, create `~/.config/containers/systemd/stry-dev.build.d/path.conf`:

```ini
[Build]
SetWorkingDirectory=%h/code/stry
File=%h/code/stry/containers/runtimes/development/Containerfile
IgnoreFile=%h/code/stry/containers/runtimes/development/dockerignore
```

and `~/.config/containers/systemd/stry-dev-source.volume.d/path.conf`:

```ini
[Volume]
Device=%h/code/stry
```

Then run `systemctl --user daemon-reload`. The `stry-dev-source` volume is created once, so if you move your checkout later, stop `stry-dev` and remove it (`podman volume rm stry-dev-source`) before you start it again. Removing it never deletes your checkout.

### Admin account

For testing, you can seed a super-admin user:

```bash
lpod stry-dev a db:seed --class=AdminSeeder
```

:::warning
Only use this seeder for testing. Never run it in production. See the [security checklist](production.md#security-checklist).
:::

You can also create an admin interactively, without the seeder: `lpod stry-dev artisan users:create --super-admin` (see [CLI Interaction](interaction.md#users)).

## VS Code Dev Containers

`containers/devcontainer/devcontainer.json` is a config for the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers), so you can develop **stry** inside a container. This is separate from the units above, which run the app as a service. It connects to the `systemd-stry-dev` network and gives you PHP IntelliSense, debugging, a terminal and the Claude Code and Codex CLIs inside the container. Its image is the `devcontainer` stage of `containers/runtimes/development/Containerfile`: the development image plus Git, the GitHub and AWS CLIs, Claude Code, Codex and Laravel LSP. The first start builds it, which takes a while. Symlink it, so it stays up to date when you pull:

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
lpod stry-dev artisan ide-helper:generate
lpod stry-dev artisan ide-helper:meta
lpod stry-dev artisan ide-helper:models --nowrite
```

## AI-assisted development

[Laravel Boost](https://boost.laravel.com/) is set up as an MCP server. In VS Code, open the Command Palette (`Ctrl+Shift+P` or `Cmd+Shift+P`), choose **MCP: List Servers** and start `laravel-boost`.

The devcontainer (see [above](#vs-code-dev-containers)) also has the `claude` and `codex` CLIs. Your `~/.claude` and `~/.codex` folders are mounted into the container, so you stay logged in after a rebuild. Use either CLI together with Boost, which gives it Laravel-specific context such as routes, the database schema, config and Tinker.

## Testing and code quality

```bash
lpod stry-dev artisan test
lpod stry-dev artisan test --filter=testMethodName
lpod stry-dev bin pint
lpod stry-dev bin larastan
```

## Admin services

Available when you're logged in as a **super-admin**:

| Service       | URL                 | Description                          |
| ------------- | ------------------- | ------------------------------------ |
| **Horizon**   | `APP_URL/horizon`   | Monitor and manage queues            |
| **Telescope** | `APP_URL/telescope` | Debugging tool (only in development) |

## Troubleshooting

- **A container won't start**: run `journalctl --user -u stry-dev -f`. Look for a missing or invalid `.env` in your checkout, or, if you published the app's port with a drop-in, another process using that port.
- **Permission errors**: run `sudo chown -R "$USER:" ~/projects/stry/storage`. The containers run as your own user, so this is only needed after running a command as root, such as `podman exec` without `--user docker`.
- **Permission denied on `/media` or `/import`**: the development units keep the library in `storage/app/library/` in your checkout. If you gave `stry-dev-videos` or the others another folder with a drop-in (see [Another library folder](podman.md#another-library-folder)), label it once for containers on SELinux hosts. See [Storage, ownership & SELinux](podman.md#storage-ownership--selinux).
- **The database or caches are empty after updating the units**: the development units used to share their names with production (`stry`, `stry-pgsql`, `stry-cache` and so on), and are now named `stry-dev`. Remove the old ones with `podman quadlet rm` (`podman quadlet list` shows them; leave an installed production stry alone), install the new ones, and they start with new volumes. Your old data is still in the `stry-*` volumes. For the database, run `lpod stry-dev artisan migrate --seed` to start fresh, or copy the old volume over while the services are stopped: `podman volume export stry-pgsql | podman volume import stry-dev-pgsql -`. Once you no longer need them, remove the old volumes with `podman volume rm`.
- **Assets don't build**: run `rm -rf bootstrap/ssr && lpod stry-dev npm run build`.
- **Tests fail with `could not translate host name "systemd-stry-pgsql"`**: you ran `php artisan test` on your machine instead of inside the container network. Start the containers with `lpod stry-dev up`, then run `lpod stry-dev artisan test`.

## Next steps

- [CLI Interaction](interaction.md) for stry's Artisan commands
- [Application Configuration](configuration.md) for app settings

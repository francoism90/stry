---
title: Production Setup
section: Getting Started
order: 3
tags:
    - production
    - deployment
    - security
---

# Production Setup

## What you need

- A Linux server with systemd, a user to run **stry** as, and a public IP or domain
- [Podman 6+](https://podman.io/) with Quadlet support
- Linger for that user, so the services start at boot: `sudo loginctl enable-linger "$USER"`

CI builds the app as a container image and publishes it to `ghcr.io/francoism90/stry`, so your server doesn't need PHP, Composer or the source code to run **stry**. It needs two things:

- the Quadlet units in `containers/quadlets/production/`, the config files that tell Podman and systemd how to run each service
- your `.env` file, stored as a Podman secret

## Configure

Start from `.env.example`:

```bash
curl -fsSL https://raw.githubusercontent.com/francoism90/stry/main/.env.example -o .env
```

Edit `.env` and set at least:

- `APP_ENV=production` and `APP_DEBUG=false`
- `APP_URL`
- `APP_KEY`: generate one with `openssl rand -base64 32` and prefix it with `base64:`
- your database and S3 credentials

See [Application Configuration](configuration.md) for all options.

The app reads your library from `/srv/library/videos` and imports from `/srv/library/import`. To use other folders, give those volumes a drop-in before you start the services (see [Another library folder](podman.md#another-library-folder)). On SELinux hosts, such as Fedora, label these folders once before starting the services, or the containers can't read them. See [Storage, ownership & SELinux](podman.md#storage-ownership--selinux).

## Install

**With [Pods](https://github.com/foxws/pods):** add an application from Git with the repository `https://github.com/francoism90/stry.git` and a release tag, and choose the **production** source. Paste your `.env` as `stry-env`: Pods fills the database, Typesense and RustFS secrets in from it (see [Podman Quadlet](podman.md#with-pods)).

**By hand:**

```bash
git clone https://github.com/francoism90/stry.git ~/projects/stry
cd ~/projects/stry
podman quadlet install --application=stry containers/quadlets/production

podman secret create stry-env /path/to/.env
lpod stry-pgsql secrets       # DB_DATABASE, DB_USERNAME and DB_PASSWORD from .env
lpod stry-typesense secrets   # TYPESENSE_API_KEY
lpod stry-rustfs secrets      # AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY

systemctl --user start stry
```

`lpod SERVICE secrets` reads the `Secret=` lines of the installed unit and asks for each value. Without [`lpod`](https://github.com/foxws/lpod), create each secret with `podman secret create NAME -` and the value on stdin; the names are in the `Secret=` lines of the units.

Next, set up object storage (see [Object Storage (S3)](s3.md)) and prepare the database and search:

```bash
lpod stry artisan app:install
# or: podman exec -it --user docker systemd-stry php artisan app:install
```

It runs the migrations, seeds the roles and permissions, and sets up the Typesense collections. It then asks whether to create the S3 buckets (`app:s3-setup`) and, when there's no super-admin yet, to create one (`users:create --super-admin`, see [CLI Interaction](interaction.md#users)). Every step is safe to run again.

Installed with [Pods](https://github.com/foxws/pods), the application page offers the same steps under "Finish setup" (`after_install` in `pods.yml`), and asks for the super-admin's name, email and password there. The password goes to `users:create --password-stdin` on stdin.

To let the app and its services sleep when nobody uses them, add the [on-demand services](podman.md#on-demand-services).

By default, `stry-horizon` gets access to `/dev/dri` for hardware-accelerated transcoding, so the server needs a GPU that provides `/dev/dri`. See [Hardware acceleration](podman.md#tuning--hardware-acceleration) for driver setup, the SELinux `setsebool` step, and how to turn this off on a server without a GPU.

Check that everything works:

```bash
systemctl --user status stry
curl -I https://your-domain/
journalctl --user -u 'stry*' -f
```

:::note
Once the services are installed, you no longer need the cloned repository on the server. The app runs from the pre-built image, and Podman and systemd already have the Quadlet units and secrets.
:::

## Security checklist

- Use strong, random secrets (`openssl rand -hex 32`) for everything you store with `lpod SERVICE secrets`. Never reuse development credentials.
- Terminate HTTPS at your own reverse proxy in front of the app. The units don't publish its port `8000` on the host, so publish it with a drop-in (see [Podman Quadlet](podman.md#the-units)) and point your proxy at it, for example your router or NAS, Nginx Proxy Manager, Traefik or Cloudflare Tunnel. See [Reverse Proxy](proxy.md) for how the app routes subdomains internally.
- Only allow ports 22, 80 and 443 through the firewall.
- Never run `AdminSeeder` or other demo or test seeders in production. Only the plain `db:seed` (`PermissionSeeder`) from the setup above is safe to run again.
- Keep Podman and the base images up to date. See [Upgrading](upgrading.md).
- Schedule automatic database backups:

    ```bash
    # Every day at 2am
    0 2 * * * lpod stry-pgsql run pg_dump -U user -d stry | gzip > /backups/stry-$(date +\%Y\%m\%d).sql.gz
    ```

## Next steps

- [Application Configuration](configuration.md) for app settings
- [CLI Interaction](interaction.md) for everyday commands
- [Upgrading](upgrading.md) for updating an existing install
- `https://your-domain/horizon` to monitor the queues (super-admin only)

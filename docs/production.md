---
title: Production Setup
order: 1
tags:
    - production
    - deployment
    - security
---

# Production Setup

## Prerequisites

- Linux with systemd, root or sudo access, a public IP/domain
- [Podman 5.3+](https://podman.io/) with Quadlet support

## Setup

```bash
cd ~/projects
git clone https://github.com/francoism90/stry.git
cd stry
composer install --no-dev
cp .env.example .env
php artisan key:generate
```

Set at least `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, and the database/S3 credentials in `.env` — these get baked into the `stry-env` Podman secret in the next step. See [Application Configuration](configuration.md) for the full list of app-specific options.

Render, install, and start the containers — see [Podman Quadlet](podman.md) for the full command list and the actual service names:

```bash
php artisan podman:setup
vendor/bin/lpod install frankenphp-octane/app.quadlets --replace
# ...install every service you need, see podman/frankenphp-octane/...
vendor/bin/lpod secrets stry
# ...and secrets for every service that needs them...
vendor/bin/lpod install proxy/proxy.quadlets --replace

vendor/bin/lpod stry up
```

Then set up object storage — see [Object Storage (S3)](s3.md) — and run migrations:

```bash
vendor/bin/lpod stry artisan podman:s3-setup
vendor/bin/lpod stry artisan migrate --force
```

`stry-horizon` passes `/dev/dri` through for hardware-accelerated transcoding by default, so the host must have a GPU with `/dev/dri` present — see [Hardware acceleration](podman.md#tuning--hardware-acceleration) for driver setup, the SELinux `setsebool` step, and how to disable it if the host has no GPU.

Verify:

```bash
systemctl --user status stry
curl -I https://your-domain/
journalctl --user -u 'stry*' -f
```

## Security checklist

- Use `openssl rand -hex 32`-strength secrets for everything stored via `lpod secrets` — never reuse development credentials.
- Terminate HTTPS with the bundled `proxy` preset (Caddy) — see [Proxy Configuration](proxy.md).
- Restrict the firewall to 22/80/443.
- Never run seeders (`db:seed`) against production data.
- Keep Podman and base images updated (`podman pull ...`, then `lpod install ... --replace`).
- Schedule automated database backups:

    ```bash
    # Daily at 2am
    0 2 * * * vendor/bin/lpod stry-pgsql run pg_dump -U user -d stry | gzip > /backups/stry-$(date +\%Y\%m\%d).sql.gz
    ```

## Next steps

- [Application Configuration](configuration.md) for app-specific settings
- [CLI Interaction](interaction.md) for day-to-day commands
- <https://stry.test/horizon> to monitor queues (super-admin only)

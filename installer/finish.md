---
title: Done
---

stry is installed and running. Open it at your `APP_URL`.

Next steps:

- Point your reverse proxy at the app's port `8000`, see [Reverse Proxy](https://github.com/francoism90/stry/blob/main/docs/proxy.md).
- Create the S3 buckets when you use RustFS or another S3 server: `podman exec systemd-stry php artisan podman:s3-setup`.
- Schedule database backups, see the [security checklist](https://github.com/francoism90/stry/blob/main/docs/production.md#security-checklist).

You can stop the installer now with Ctrl+C. It's removed when it stops.

---
name: podman-s3
description: Create S3 buckets and apply a CORS policy with foxws/laravel-podman's podman:s3-setup command, for AWS S3 or S3-compatible storage like RustFS, MinIO or Garage. Use when adding buckets, fixing browser CORS errors on stored files, or setting up object storage.
---

# S3 buckets with podman:s3-setup

`php artisan podman:s3-setup` creates buckets and applies a CORS policy through the S3 API. It uses the app's `s3` disk from `config/filesystems.php` (`key`, `secret`, `region`, `endpoint`, `use_path_style_endpoint`). Nothing else needs configuring.

**Ask first:** Never delete buckets or objects (`aws s3 rb`, `aws s3 rm`, `mc rb`, `mc rm`, or a bucket's lifecycle rule that expires objects), or the RustFS volume, without the user's explicit confirmation first. `podman:s3-setup` only creates buckets and sets CORS, so it's safe to run again. See "Destructive commands" in the `podman-lpod` skill for the full list.

## Requirements

Needs `aws/aws-sdk-php`. Without it, the command shows an error:

```bash
composer require aws/aws-sdk-php
```

## Configure buckets

Both lists are empty by default, so the command does nothing until you add buckets in `config/podman.php`:

```php
's3_buckets' => env('PODMAN_S3_BUCKETS', [
    'local', 'conversions', 'segments',
]),

's3_cors_buckets' => env('PODMAN_S3_CORS_BUCKETS', [
    'conversions', 'segments',
]),
```

- `s3_buckets`: buckets to create. Existing buckets are skipped, so it's safe to run again.
- `s3_cors_buckets`: buckets that get the CORS policy. Each must also be in `s3_buckets`.
- Both take a PHP array or a comma-separated string (in `.env`).

A bucket needs CORS when the browser loads from it directly (`<img>`, `<video>`, `fetch()`, direct uploads). Buckets only used server-side don't.

## CORS policy

The policy comes from `containers/stubs/s3/cors.json` if published, otherwise the package's bundled copy. To change it:

```bash
php artisan podman:publish s3
# edit containers/stubs/s3/cors.json (standard S3 CORS JSON)
php artisan podman:s3-setup
```

```json
{
    "CORSRules": [
        {
            "AllowedOrigins": ["*"],
            "AllowedMethods": ["GET", "HEAD"],
            "AllowedHeaders": ["*"],
            "ExposeHeaders": ["Content-Length", "Content-Range", "Accept-Ranges", "ETag"],
            "MaxAgeSeconds": 7200
        }
    ]
}
```

For browser uploads, add `PUT`/`POST` to `AllowedMethods`. In production, restrict `AllowedOrigins` to the app's domain.

## Run it

Run it in the app container, where the storage endpoint is reachable:

```bash
lpod my-app artisan podman:s3-setup
```

In the package's devcontainer, which joins the app's network, run `php artisan podman:s3-setup` directly.

With the bundled RustFS service, the endpoint is served on the `s3.` subdomain of `APP_URL` by the `proxy` preset.

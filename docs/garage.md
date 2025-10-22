---
title: Garage
order: 3
tags:
  - garage
  - s3
  - storage
  - assets
---

To learn more about MinIO, consider reading the following resources:

- <https://min.io/>
- <https://min.io/docs/minio/linux/reference/minio-mc.html>

## Prerequisites

- MinIO up-and-running
- MinIO Client `mc` (included in base-container)

## Usage

1. Make sure minio is up and running:

```bash
systemctl --user start stry-minio
```

1. Setup connection using the generated credentials of `minio.env`:

```bash
mc alias set myminio http://systemd-stry-minio:9000 <MINIO_ROOT_USER>
mc admin info myminio
```

1. Generate admin credentials:

```bash
mc admin user svcacct add myminio <MINIO_ROOT_USER>
```

1. Set both values of `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` in `.env`.

1. Create required buckets (add your own if required):

```bash
mc mb myminio/local
mc mb myminio/assets
mc mb myminio/conversions
mc mb myminio/segments
mc mb myminio/secrets
```

1. Set anonymous `download` permissions on required buckets:

```bash
mc anonymous set download myminio/assets
mc anonymous set download myminio/conversions
mc anonymous set download myminio/segments
```

## Disable bucket listing

> **NOTE**: Disabling bucket listing is optional, but highly recommended on production.

1. Export current bucket permissions:

```bash
cd /tmp
mc anonymous get-json myminio/assets > assets.json
mc anonymous get-json myminio/conversions > conversions.json
mc anonymous get-json myminio/segments > segments.json
```

1. Remove the `"s3:ListBucket"` from the `Action` array in each `<bucket>.json` file:

```bash
vi assets.json
vi conversions.json
vi segments.json
```

1. Update the bucket policy:

```bash
mc anonymous set-json assets.json myminio/assets
mc anonymous set-json conversions.json myminio/conversions
mc anonymous set-json segments.json myminio/segments
rm -rf assets.json conversions.json segments.json
```

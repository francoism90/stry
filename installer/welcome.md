---
title: Welcome
---

This installer sets up **stry** on this server as rootless Podman Quadlet services, then removes itself.

Before you start, have these ready in the app's folder (mounted at `/workspace`):

- a `.env` with at least `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY` (`base64:` followed by `openssl rand -base64 32`) and the database, Typesense and S3 credentials
- the folders the app reads your library from: `/mnt/media/videos`, `/mnt/media/import` and `/mnt/media/exports`. On SELinux hosts, label them first, see [Storage, ownership & SELinux](https://github.com/francoism90/stry/blob/main/docs/podman.md#storage-ownership--selinux).

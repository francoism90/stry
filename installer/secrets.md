---
title: Secrets
---

The services keep the app's `.env` and their passwords in Podman secrets. The database, Typesense and RustFS secrets are read from the matching keys in your `.env`, so the app and the services use the same values.

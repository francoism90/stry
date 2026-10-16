---
title: Services
---

The installer renders the presets in your `PODMAN_DEFAULT_PRESETS` into the app's folder, installs them as Quadlets and starts them. Use `production,ondemand` on a server, or `development,ondemand` to run your checkout. Pulling or building the images can take a few minutes the first time.

With the `ondemand` preset, the app and its services sleep when nobody uses them, and wake up on the next request.

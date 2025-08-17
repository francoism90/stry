# Upgrading

> **NOTE**: See <https://docs.podman.io/en/latest/markdown/podman-auto-update.1.html> for details.

1. Sync project with latest changes:

```bash
cd ~/projects/stry
git pull
```

1. Enable the `podman-auto-update.timer`:

```bash
systemctl --user enable podman-auto-update.timer --now
```

1. Restart the `stry.service` to force a rebuild:

```bash
systemctl --user restart stry
```

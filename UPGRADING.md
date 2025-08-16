# Upgrading

1. Enable the `podman-auto-update.timer`:

```bash
systemctl --user enable podman-auto-update.timer --now
```

1. Sync project with latest changes:

```bash
cd ~/projects/stry
git pull
```

It should manage updates automatically, or use the `stry-build.service` to force a rebuild.

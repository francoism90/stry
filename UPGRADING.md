# Upgrading

> **TIP:** You may want to enable `podman-auto-update.timer` to automatically update containers daily.

1. Sync with the latest changes:

```fish
cd ~/projects/stry
git pull
```

1. Rebuild containers (you may want to do this on weekly):

```fish
./bin/make-containers --no-cache
```

1. To update the application:

```fish
stry composer install
stry pnpm install && stry pnpm build
stry a app:update --assets
```

1. Restart the affected containers:

```fish
systemctl --user restart stry stry-queue stry-reverb stry-schedule
```

or

```fish
systemctl --user restart podman-auto-update.service
```

---
paths:
  - '.devcontainer/**'
---

# General

## Devcontainer changes need manual copy + rebuild
`foxws/laravel-podman` renders the `devcontainer` preset (from `containers/stubs/devcontainer` if published, otherwise the vendor stub) to `podman/devcontainer/runtimes/devcontainer.json` — it does NOT write to the root `.devcontainer/` that VS Code Dev Containers actually reads. After running `php artisan podman:generate devcontainer` (or `podman:setup`), manually copy the rendered `podman/devcontainer/runtimes/devcontainer.json` over `.devcontainer/devcontainer.json`, then use VS Code's "Rebuild Container" for changes to take effect. `.devcontainer/devcontainer.json` is gitignored and untracked (see `/.devcontainer` in .gitignore) since it bakes in the local host's UID/GID.

#!/usr/bin/env bash
set -euo pipefail

# Renumber "docker" to the host's PUID/PGID before dropping from root --
# lets the same prebuilt (UID/GID 1000) image work on any host UID.
if [ "$(id -u)" = '0' ]; then
    PUID=${PUID:-1000}
    PGID=${PGID:-1000}

    if [ "$(id -g docker)" != "${PGID}" ]; then
        groupmod -o -g "${PGID}" docker
    fi

    if [ "$(id -u docker)" != "${PUID}" ]; then
        usermod -o -u "${PUID}" docker
    fi

    # Non-recursive: .ssh is a read-only bind mount; chown -R would fail on it
    chown docker:docker /home/docker
    chown -R docker:docker "${PNPM_HOME}"

    exec gosu docker "$0" "$@"
fi

exec "$@"

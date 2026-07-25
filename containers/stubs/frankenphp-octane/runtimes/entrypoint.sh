#!/usr/bin/env bash
set -euo pipefail

# Renumber the "docker" user/group to match the host's PUID/PGID, then drop
# from root down to it. The image itself is always built with UID/GID 1000;
# this is what lets one shared, prebuilt image still write correctly-owned
# files on hosts where the deploying user isn't 1000.
if [ "$(id -u)" = '0' ]; then
    PUID=${PUID:-1000}
    PGID=${PGID:-1000}

    if [ "$(id -g docker)" != "${PGID}" ]; then
        groupmod -o -g "${PGID}" docker
    fi

    if [ "$(id -u docker)" != "${PUID}" ]; then
        usermod -o -u "${PUID}" docker
    fi

    chown -R docker:docker /app/storage /app/bootstrap/cache

    exec gosu docker "$0" "$@"
fi

APP_COMMAND=${APP_COMMAND:-'/usr/bin/bash'}

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

# Set up SQLite database
if [ ! -f "/app/database/database.sqlite" ]; then
    log "INFO" "Creating SQLite database..."
    touch /app/database/database.sqlite
fi

# Set up environment configuration
if [ ! -f "/app/.env" ]; then
    log "ERROR" "Missing /app/.env. Provide a Laravel env file mounted at: /app/.env"
    exit 1
fi

log "INFO" "Loading runtime environment configuration from /app/.env..."

# Ensure APP_KEY is provided in runtime configuration
if ! grep -q '^APP_KEY=.' /app/.env && [ -z "${APP_KEY:-}" ]; then
    GENERATED_KEY="$(${FRANKEN_CLI} key:generate --show || true)"

    if [ -n "${GENERATED_KEY}" ]; then
        log "ERROR" "APP_KEY is missing from runtime configuration. Paste this line into app.env: APP_KEY=${GENERATED_KEY}"
    else
        log "ERROR" "APP_KEY is missing from runtime configuration and generation failed."
    fi

    exit 1
fi

# Clear any stale caches
log "INFO" "Clearing stale caches..."
${FRANKEN_CLI} optimize:clear

# Create storage symlinks
log "INFO" "Creating storage symlinks..."
${FRANKEN_CLI} storage:link

# Create PWA manifest
log "INFO" "Creating PWA manifest..."
${FRANKEN_CLI} pwa:generate

# Ensure all caches are warmed up
log "INFO" "Optimizing application..."
${FRANKEN_CLI} optimize

# Run the provided command
log "INFO" "Starting command..."
exec ${APP_COMMAND}

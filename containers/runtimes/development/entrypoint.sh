#!/usr/bin/env bash
set -euo pipefail

# Unlike production, this image is built locally with the host's UID/GID
# (see the app quadlet's BuildArg=UID/GID) and the Containerfile ends as
# USER docker, not root -- so there's no renumbering or chown to do here.

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
    GENERATED_KEY="$(${PHP_CLI} key:generate --show || true)"

    if [ -n "${GENERATED_KEY}" ]; then
        log "ERROR" "APP_KEY is missing from runtime configuration. Paste this line into app.env: APP_KEY=${GENERATED_KEY}"
    else
        log "ERROR" "APP_KEY is missing from runtime configuration and generation failed."
    fi

    exit 1
fi

# Drop config, route and event caches left behind by an `optimize`, so code
# edits show up. The application cache holds data rather than build output,
# so it is kept, and compiled views already recompile when a view changes.
declare -A STALE_CACHES=(
    [config.php]=config:clear
    [routes-v7.php]=route:clear
    [events.php]=event:clear
)

for file in "${!STALE_CACHES[@]}"; do
    if [ -f "/app/bootstrap/cache/${file}" ]; then
        log "INFO" "Clearing stale ${file}..."
        ${PHP_CLI} "${STALE_CACHES[$file]}"
    fi
done

# Run the provided command
log "INFO" "Starting command..."
exec ${APP_COMMAND}

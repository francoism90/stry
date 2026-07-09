#!/usr/bin/env bash
set -euo pipefail

APP_SERVICE=${APP_SERVICE:-'app'}
APP_RUNTIME_ENV=${APP_RUNTIME_ENV:-'production'}

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

# Ensure cache temp directories exist (named volume may be empty after rebuild)
log "INFO" "Ensuring cache temp directories exist..."
mkdir -p /cache/temp/{ffmpeg,packager,streamer,ab-av1}

# Set up SQLite database
if [ ! -f "database/database.sqlite" ]; then
    log "INFO" "Creating SQLite database..."
    touch database/database.sqlite
fi

# Set up environment configuration
if [ ! -f "/config/app.env" ]; then
    log "ERROR" "Missing /config/app.env. Provide a Laravel env file mounted at /config/app.env."
    exit 1
fi

log "INFO" "Loading runtime environment configuration from /config/app.env..."
cp /config/app.env /app/.env

# Ensure APP_KEY is provided in runtime configuration
if ! grep -q '^APP_KEY=.' /app/.env && [ -z "${APP_KEY:-}" ]; then
    GENERATED_KEY="$(${ARTISAN} key:generate --show || true)"

    if [ -n "${GENERATED_KEY}" ]; then
        log "ERROR" "APP_KEY is missing from runtime configuration. Paste this line into app.env: APP_KEY=${GENERATED_KEY}"
    else
        log "ERROR" "APP_KEY is missing from runtime configuration and generation failed."
    fi

    exit 1
fi

# Clear any stale caches
log "INFO" "Clearing stale caches..."
${ARTISAN} optimize:clear

# Create storage symlinks
log "INFO" "Creating storage symlinks..."
${ARTISAN} storage:link

# Application-specific setup
if [ "${APP_SERVICE}" = "app" ] && [ "${APP_RUNTIME_ENV}" = "production" ]; then
    # Ensure migrations are up to date
    log "INFO" "Running any pending migrations..."
    ${ARTISAN} migrate --force

    # Generate PWA assets
    log "INFO" "Generating PWA assets..."
    ${ARTISAN} pwa:generate

    # Ensure scout settings are synced
    log "INFO" "Syncing scout settings..."
    ${ARTISAN} scout:sync
fi

# Optimize for production
if [ "${APP_RUNTIME_ENV}" = "production" ]; then
    # Ensure package structures are cached
    log "INFO" "Optimizing packages..."
    ${ARTISAN} data:cache-structures

    # Ensure all caches are warmed up
    log "INFO" "Optimizing application..."
    ${ARTISAN} optimize
fi

log "INFO" "App service: ${APP_SERVICE}"
case ${APP_SERVICE} in
    app)
        log "INFO" "Starting Octane..."
        exec ${OCTANE}
        ;;
    ssr)
        log "INFO" "Starting SSR..."
        exec ${ARTISAN} inertia:start-ssr
        ;;
    horizon)
        log "INFO" "Starting Horizon..."
        exec ${PHP_CLI} /app/artisan horizon
        ;;
    reverb)
        log "INFO" "Starting Reverb..."
        exec ${PHP_CLI} /app/artisan reverb:start
        ;;
    scheduler)
        log "INFO" "Starting Scheduler..."
        exec ${PHP_CLI} /app/artisan schedule:work
        ;;
    shell)
        log "INFO" "Starting shell..."
        exec /usr/bin/env bash
        ;;
    *)
        log "ERROR" "Unknown app service: ${APP_SERVICE}"
        exit 1
        ;;
esac

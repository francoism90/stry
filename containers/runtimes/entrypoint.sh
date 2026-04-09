#!/usr/bin/env bash
set -euo pipefail

CONTAINER_ENV=${CONTAINER_ENV:-'production'}
CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

# Set watch on development
if [ "${CONTAINER_ENV}" = "development" ]; then
    log "INFO" "Listen for application changes..."
    OCTANE="${OCTANE} --watch"
fi

# Set up SQLite database
if [ ! -f "database/database.sqlite" ]; then
    log "INFO" "Creating SQLite database..."
    touch database/database.sqlite
fi

# Set up environment configuration
if [ ! -f ".env" ]; then
    log "INFO" "Creating environment configuration..."
    cp .env.example .env
    ${ARTISAN} key:generate
fi

 # Clear current cache
log "INFO" "Clearing cache..."
${ARTISAN} optimize:clear

# Link storage
log "INFO" "Linking storage..."
${ARTISAN} storage:link

# Application-specific setup
if [ "${CONTAINER_ROLE}" = "app" ]; then
    # Ensure migrations are up to date
    log "INFO" "Running any pending migrations..."
    ${ARTISAN} migrate --force

    # Ensure scout settings are synced
    log "INFO" "Syncing scout settings..."
    ${ARTISAN} scout:sync
fi

# Handle assets
if [[ "${CONTAINER_ROLE}" = "app" || "${CONTAINER_ROLE}" = "ssr" ]]; then
    # Fetch Google Fonts
    log "INFO" "Fetching Google Fonts..."
    ${ARTISAN} google-fonts:fetch

    # Regenerate PWA assets
    log "INFO" "Generating PWA assets..."
    ${ARTISAN} pwa:generate
fi

# Optimize application for production
if [ "${CONTAINER_ENV}" = "production" ]; then
    log "INFO" "Optimizing application..."
    ${ARTISAN} optimize
    ${ARTISAN} data:cache-structures
fi

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
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
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac

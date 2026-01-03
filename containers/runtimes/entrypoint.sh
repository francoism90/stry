#!/usr/bin/env bash
set -e

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

# Install PHP dependencies via Composer
if [ ! -d "vendor" ]; then
    log "INFO" "Installing PHP dependencies..."
    composer install
fi

# Install Node.js dependencies via pnpm
if [ ! -d "node_modules" ]; then
    log "INFO" "Installing Node.js dependencies..."
    pnpm install
fi

# Set up environment configuration
if [ ! -f ".env" ]; then
    log "INFO" "Creating environment configuration..."
    cp .env.example .env
    ${ARTISAN} key:generate
fi

# Set up application
if [ "${CONTAINER_ENV}" = "production" ]; then
    # Optimize application
    log "INFO" "Optimizing application..."
    ${ARTISAN} optimize

    # Link storage
    log "INFO" "Linking storage..."
    ${ARTISAN} storage:link

    # Cache configurations
    log "INFO" "Caching configurations..."
    ${ARTISAN} data:cache-structures
fi

# Perform role-specific setup
if [[ "${CONTAINER_ROLE}" = "app" && "${CONTAINER_ENV}" = "production" ]]; then
    # Ensure database is up to date
    log "INFO" "Running any pending migrations..."
    ${ARTISAN} migrate --seed --force

    # Ensure assets are fetched
    log "INFO" "Fetching Google Fonts..."
    ${ARTISAN} google-fonts:fetch

    # Cache views
    log "INFO" "Caching views..."
    ${ARTISAN} view:cache
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
        exec ${ARTISAN} horizon
        ;;
    scheduler)
        log "INFO" "Starting Scheduler..."
        exec ${ARTISAN} schedule:work
        ;;
    reverb)
        log "INFO" "Starting Reverb..."
        exec ${ARTISAN} reverb:start
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

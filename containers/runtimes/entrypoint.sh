#!/usr/bin/env bash
set -e

CONTAINER_ENV=${CONTAINER_ENV:-'production'}
CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
ARTISAN="php -d variables_order=EGPCS /app/artisan"
OCTANE="${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080"

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

# Prepare application
log "INFO" "Preparing application..."

# Watch for changes on development
if [ "${CONTAINER_ENV}" = "development" ]; then
    log "INFO" "Listen for application changes..."
    OCTANE="${OCTANE} --watch"
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

# Set up SQLite database
if [ ! -f "database/database.sqlite" ]; then
    log "INFO" "Creating SQLite database..."
    touch database/database.sqlite
fi

# Ensure caches are invalid
log "INFO" "Flush the application cache..."
${ARTISAN} cache:clear

# Ensure database is up to date
log "INFO" "Running any pending migrations..."
${ARTISAN} migrate --seed --force

# Set up symbolic links
log "INFO" "Creating symbolic links..."
${ARTISAN} storage:link

# Set up assets
if [ "${CONTAINER_ROLE}" = "app" ]; then
    log "INFO" "Fetching Google Fonts..."
    ${ARTISAN} google-fonts:fetch
fi

# Optimize application
if [ "${CONTAINER_ENV}" = "production" ]; then
    log "INFO" "Optimizing application..."
    ${ARTISAN} optimize
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
    *)
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac
